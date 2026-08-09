<?php

namespace App\Services\Operations;

use App\DTOs\OrderDTO;
use App\DTOs\PriceBreakdownDTO;
use App\Enums\OrderEnums\OrderMaterialStatus;
use App\Enums\OrderEnums\OrderStatus;
use App\Enums\OrderEnums\OrderSubServiceStatus;
use App\Enums\PaymentEnums\PaymentMethod;
use App\Exceptions\BookingBranchInactiveException;
use App\Exceptions\BookingCompanyInactiveException;
use App\Models\Car;
use App\Models\Company;
use App\Models\Service;
use App\Models\SubService;
use App\Models\User;
use App\Repositories\Eloquent\BranchRepository;
use App\Repositories\Eloquent\MaterialRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\ServiceRepository;
use App\Services\Operations\Booking\BookingTypeHandlerResolver;
use App\Services\Operations\Payment\PaymentMethodHandlerResolver;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Orchestrates the two-step booking flow: quote() validates + prices the
 * submission and caches it under a short-lived token; confirm() redeems
 * that token and creates the real order(s) — one per car. Company
 * bookings (which may cover multiple cars) share a booking_group_id;
 * personal bookings (always exactly one car) get none.
 */
class BookingQuoteService
{
    protected const QUOTE_TTL_MINUTES = 15;

    public function __construct(
        protected OrderRepository $orderRepository,
        protected ServiceRepository $serviceRepository,
        protected BranchRepository $branchRepository,
        protected MaterialRepository $materialRepository,
        protected PricingEngineService $pricingEngine,
        protected BookingTypeHandlerResolver $bookingTypeHandlerResolver,
        protected PaymentMethodHandlerResolver $paymentMethodHandlerResolver,
    ) {}

    public function quote(array $data): array
    {
        $customer = auth()->user();

        //check if cars belongs to same customer company and if personal customer has only one car
        $carIds = $this->resolveCarIds($customer, $data);
        $cars = Car::with('carType')->whereIn('id', $carIds)->get()->keyBy('id');

        //return object of the booking type handler that matches the data, or throw ValidationException if none match
        $bookingTypeHandler = $this->bookingTypeHandlerResolver->resolve($data);
        $bookingTypeHandler->validate($data);

        $service = $this->serviceRepository->findById((int) $data['service_id']);
        //category is derived from the booked service, not accepted from the client, so it can never mismatch
        $data['category_id'] = $service->category_id;
        $branch = isset($data['branch_id']) ? $this->branchRepository->findByIdOrNull((int) $data['branch_id']) : null;
        //if user chose booking type scheduled he must add date or if he chose immediate then scheduled wil be now
        $scheduledAt = isset($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : now();

        //check if branch is active
        if ($branch && ! $branch->is_active) {
            throw new BookingBranchInactiveException();
        }

        //if customer was company the function will return company info or null if personal customer
        $company = $this->resolveCompany($customer);
        $isCompanyCustomer = $company !== null;

        //check if company is active
        if ($company && ! $company->is_active) {
            throw new BookingCompanyInactiveException();
        }

        $paymentMethod = PaymentMethod::from($data['payment_method']);
        $paymentHandler = $this->paymentMethodHandlerResolver->resolve($paymentMethod);

        $context = ['service' => $service, 'car_ids' => $carIds];

        //customer-chosen add-ons apply once per booking, the same flat amount on every car in the group
        $selectedSubServices = $this->resolveSubServices($service, $data['sub_service_ids'] ?? []);
        $selectedMaterials = $this->resolveMaterials($data['materials'] ?? []);

        //جيب سعر كل خدمة فرعية
        $subServicePrice = round((float) $selectedSubServices->sum(fn (SubService $subService) => (float) $subService->price), 2);

        //جبلي السعر الكامل لكل مادة مستخمة حتى ولو كانت المادة اكثر من قطعة
        $materialsPrice = round((float) collect($selectedMaterials)->sum(fn (array $material) => $material['total_price']), 2);

        // Price varies per car (vehicle type scales the base price), so each
        // car in the group gets its own breakdown rather than one shared
        // breakdown multiplied by car count.

        //array for save details for every car for company
        $carBreakdowns = [];

        //price for all car in booking
        $totalForGroup = 0.0;

        foreach ($carIds as $carId) {
            $priceMultiplier = (float) ($cars[$carId]->carType?->price_multiplier ?? 1.0);

            $breakdown = $paymentHandler->pricingIsSkipped()
                ? $this->coveredByPackageBreakdown()
                : $this->pricingEngine->calculate(
                    service: $service,
                    isVip: (bool) ($data['is_vip'] ?? false),
                    distanceKm: isset($data['distance_km']) ? (float) $data['distance_km'] : null,
                    scheduledAt: $scheduledAt,
                    isImmediate: (bool) ($data['booking_type'] ?? false),
                    priceMultiplier: $priceMultiplier,
                    isCompanyCustomer: $isCompanyCustomer,
                );

            $servicePrice = round($breakdown->servicePrice, 2);
            $totalPrice = round($servicePrice + $subServicePrice + $materialsPrice, 2);

            $carBreakdowns[$carId] = [
                'items' => $breakdown->items,
                'discount_amount' => $breakdown->discountAmount,
                'service_price' => $servicePrice,
                'sub_service_price' => $subServicePrice,
                'materials_price' => $materialsPrice,
                'total_price' => $totalPrice,
            ];
            $totalForGroup += $totalPrice;
        }

        $totalForGroup = round($totalForGroup, 2);

        $paymentHandler->validate($customer, $totalForGroup, $context);

        $token = (string) Str::uuid();

        Cache::put($token, [
            'customer_id' => $customer->id,
            'car_ids' => $carIds,
            'data' => $data,
            'car_breakdowns' => $carBreakdowns,
            'sub_services' => $selectedSubServices->map(fn (SubService $subService) => [
                'sub_service_id' => $subService->id,
                'price' => (float) $subService->price,
            ])->all(),
            'materials' => $selectedMaterials,
            'payment_method' => $paymentMethod->value,
        ], now()->addMinutes(self::QUOTE_TTL_MINUTES));

        return [
            'quote_token' => $token,
            'branch' => $branch,
            'sub_services' => $selectedSubServices->values(),
            'materials' => $selectedMaterials,
            'cars' => collect($carIds)->map(fn (int $carId) => [
                'car_id' => $carId,
                'price_items' => $carBreakdowns[$carId]['items'],
                'service_price' => $carBreakdowns[$carId]['service_price'],
                'sub_service_price' => $carBreakdowns[$carId]['sub_service_price'],
                'materials_price' => $carBreakdowns[$carId]['materials_price'],
                'total_price' => $carBreakdowns[$carId]['total_price'],
            ])->all(),
            'car_count' => count($carIds),
            'total_price' => $totalForGroup,
            'expires_at' => now()->addMinutes(self::QUOTE_TTL_MINUTES)->toIso8601String(),
        ];
    }

    /**
     * Sub-services the customer picked, restricted to ones belonging to the
     * booked service and active — throws if any requested id doesn't match.
     */
    protected function resolveSubServices(Service $service, array $subServiceIds): Collection
    {
        $subServiceIds = array_values(array_unique(array_map('intval', $subServiceIds)));

        if ($subServiceIds === []) {
            return collect();
        }

        $subServices = SubService::whereIn('id', $subServiceIds)
            ->where('service_id', $service->id)
            ->where('is_active', true)
            ->get();

        if ($subServices->count() !== count($subServiceIds)) {
            throw ValidationException::withMessages([
                'sub_service_ids' => [__('One or more selected sub-services are invalid for this service.')],
            ]);
        }

        return $subServices;
    }

    /**
     * Materials the customer picked (id + quantity), restricted to ones
     * visible to customers and active — throws if any requested id doesn't
     * match or is duplicated.
     */
    protected function resolveMaterials(array $materialsInput): array
    {
        if ($materialsInput === []) {
            return [];
        }

        $materialIds = array_map(fn (array $row) => (int) $row['material_id'], $materialsInput);

        if (count($materialIds) !== count(array_unique($materialIds))) {
            throw ValidationException::withMessages([
                'materials' => [__('Duplicate materials are not allowed in the same booking.')],
            ]);
        }

        $materials = $this->materialRepository->findManyByIds($materialIds);

        if ($materials->count() !== count($materialIds)) {
            throw ValidationException::withMessages([
                'materials' => [__('One or more selected materials are invalid.')],
            ]);
        }

        return collect($materialsInput)->map(function (array $row) use ($materials) {
            $material = $materials->get((int) $row['material_id']);

            if (! $material->is_active || ! $material->is_visible_to_customer) {
                throw ValidationException::withMessages([
                    'materials' => [__('One or more selected materials are not available for booking.')],
                ]);
            }

            $quantity = (float) $row['quantity'];
            $unitPrice = (float) $material->unit_price;

            return [
                'material_id' => $material->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => round($unitPrice * $quantity, 2),
            ];
        })->all();
    }

    public function confirm(string $quoteToken): array
    {
        $payload = Cache::pull($quoteToken);

        if (! $payload || $payload['customer_id'] !== auth()->id()) {
            throw ValidationException::withMessages([
                'quote_token' => [__('This quote has expired or is invalid. Please request a new one.')],
            ]);
        }

        $data = $payload['data'];
        $service = $this->serviceRepository->findById((int) $data['service_id']);
        $bookingTypeHandler = $this->bookingTypeHandlerResolver->resolve($data);
        $paymentHandler = $this->paymentMethodHandlerResolver->resolve(PaymentMethod::from($payload['payment_method']));
        //array contain every car with its price info and total price
        $totalAmountForGroup = round(array_sum(array_column($payload['car_breakdowns'], 'total_price')), 2);
        $context = ['service' => $service, 'car_ids' => $payload['car_ids'], 'total_amount_for_group' => $totalAmountForGroup];

        $customer = User::findOrFail($payload['customer_id']);
        $companyId = $this->resolveCompany($customer)?->id;

        //immediate bookings are scheduled 30 minutes from the moment the order is actually confirmed, not from when the quote was taken
        if ((int) $data['booking_type'] === 1) {
            $scheduledAt = now()->addMinutes(60);
        } else {
            $scheduledAt = Carbon::parse($data['scheduled_at']);
        }

        return DB::transaction(function () use ($payload, $data, $scheduledAt, $bookingTypeHandler, $paymentHandler, $context, $companyId) {
            $groupId = $companyId !== null ? (string) Str::uuid() : null;
            $orders = [];

            foreach ($payload['car_ids'] as $carId) {
                $carBreakdown = $payload['car_breakdowns'][$carId];

                $dto = OrderDTO::fromArray([
                    ...$data,
                    'scheduled_at' => $scheduledAt->toDateTimeString(),
                    'booking_group_id' => $groupId,
                    'customer_id' => $payload['customer_id'],
                    'company_id' => $companyId,
                    'car_id' => $carId,
                    'discount_amount' => $carBreakdown['discount_amount'],
                    'service_price' => $carBreakdown['service_price'],
                    'sub_service_price' => $carBreakdown['sub_service_price'],
                    'materials_price' => $carBreakdown['materials_price'],
                    //total price for pricing rule and condition only
                    'total_price' => $carBreakdown['total_price'],
                    'status' => OrderStatus::PENDING->value,
                ]);

                $order = $this->orderRepository->create($dto);
                $order->priceItems()->createMany($carBreakdown['items']);

                if (! empty($payload['sub_services'])) {
                    $order->subServices()->createMany(collect($payload['sub_services'])->map(fn (array $subService) => [
                        'sub_service_id' => $subService['sub_service_id'],
                        'price' => $subService['price'],
                        'status' => OrderSubServiceStatus::PENDING->value,
                    ])->all());
                }

                if (! empty($payload['materials'])) {
                    $order->materials()->createMany(collect($payload['materials'])->map(fn (array $material) => [
                        'material_id' => $material['material_id'],
                        'requested_by' => $payload['customer_id'],
                        'quantity' => $material['quantity'],
                        'unit_price' => $material['unit_price'],
                        'total_price' => $material['total_price'],
                        'status' => OrderMaterialStatus::APPROVED->value,
                        'approved_at' => now(),
                    ])->all());
                }

                $bookingTypeHandler->afterCreate($order, $data);
                $paymentHandler->settle($order, $carBreakdown['total_price'], $context);

                $orders[] = $order->load(['priceItems', 'payments', 'subServices.subService', 'materials.material']);
            }

            return $orders;
        });
    }

    /**
     * A personal customer may book exactly one car; a company customer may
     * book one or more, as long as every car belongs to them.
     */
    protected function resolveCarIds(User $customer, array $data): array
    {
        $carIds = array_values(array_unique(array_map('intval', $data['car_ids'] ?? [])));

        if ($carIds === []) {
            throw ValidationException::withMessages([
                'car_ids' => [__('Select at least one car.')],
            ]);
        }

        if (count($carIds) > 1 && ! $customer->hasRole('customer_company')) {
            throw ValidationException::withMessages([
                'car_ids' => [__('Personal customers may only book one car at a time.')],
            ]);
        }

        $ownedCount = Car::whereIn('id', $carIds)->where('user_id', $customer->id)->count();

        if ($ownedCount !== count($carIds)) {
            throw ValidationException::withMessages([
                'car_ids' => [__('One or more selected cars do not belong to you.')],
            ]);
        }

        return $carIds;
    }

    /**
     * The requesting company customer's company, or null for a personal
     * customer. Used both to gate on is_active before quoting and to stamp
     * company_id / booking_group_id when the order is actually created.
     */
    protected function resolveCompany(User $customer): ?Company
    {
        if (! $customer->hasRole('customer_company')) {
            return null;
        }

        return Company::where('customer_id', $customer->id)->first();
    }

    protected function coveredByPackageBreakdown(): PriceBreakdownDTO
    {
        return new PriceBreakdownDTO(
            items: [['pricing_rule_id' => null, 'label' => 'Covered by Package', 'amount' => 0.0]],
            discountAmount: 0.0,
            servicePrice: 0.0,
        );
    }
}
