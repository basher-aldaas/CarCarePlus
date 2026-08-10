<?php

namespace App\Services\Operations;

use App\DTOs\OrderDTO;
use App\Enums\OrderEnums\OrderMaterialStatus;
use App\Enums\OrderEnums\OrderStatus;
use App\Enums\OrderEnums\OrderSubServiceStatus;
use App\Enums\PaymentEnums\PaymentMethod;
use App\Exceptions\BookingBranchInactiveException;
use App\Exceptions\BookingCompanyInactiveException;
use App\Models\Branch;
use App\Models\Car;
use App\Models\Company;
use App\Models\Inventory;
use App\Models\Service;
use App\Models\SubService;
use App\Models\User;
use App\Repositories\Eloquent\BranchRepository;
use App\Repositories\Eloquent\MaterialRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\ServiceRepository;
use App\Services\Operations\Booking\BookingTypeHandlerResolver;
use App\Services\Operations\Package\PackageCoverageService;
use App\Services\Operations\Payment\PaymentMethodHandlerResolver;
use App\Support\GeoDistance;
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
        protected PackageCoverageService $packageCoverageService,
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
        //pick the branch (nearest to the customer, unless they chose one) and derive distance_km from it
        [$branch, $data] = $this->resolveBranchAndDistance($data);
        //if user chose booking type scheduled he must add date or if he chose immediate then scheduled wil be now
        $scheduledAt = isset($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : now()->addHour();

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

        //customer-chosen add-ons apply once per booking, the same flat amount on every car in the group
        $selectedSubServices = $this->resolveSubServices($service, $data['sub_service_ids'] ?? []);
        $selectedMaterials = $this->resolveMaterials($branch, $data['materials'] ?? []);

        $userPackage = null;

        if ($paymentMethod === PaymentMethod::PACKAGE) {
            $userPackageId = isset($data['user_package_id']) ? (int) $data['user_package_id'] : null;

            // The customer hasn't picked a package yet — hand back the ones
            // eligible for this service so they can choose one, instead of
            // pricing a booking we don't yet know how to cover.
            if ($userPackageId === null) {
                return [
                    'requires_package_selection' => true,
                    'eligible_packages' => $this->packageCoverageService->eligiblePackagesFor($customer, $service),
                    'branch' => $branch,
                    'distance_km' => $data['distance_km'] ?? null,
                    'sub_services' => $selectedSubServices->values(),
                    'materials' => $selectedMaterials,
                    'car_count' => count($carIds),
                ];
            }

            $userPackage = $this->packageCoverageService->resolveSelected($customer, $userPackageId, $service);
        }

        $context = ['service' => $service, 'car_ids' => $carIds, 'user_package_id' => $userPackage?->id];

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

            //every payment method is priced the same way — a package's coverage is
            //diffed against this full price afterward, never used to skip pricing it
            $breakdown = $this->pricingEngine->calculate(
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

            if ($userPackage) {
                $coverage = $this->packageCoverageService->computeCoverage(
                    $userPackage,
                    $service,
                    $breakdown,
                    $selectedSubServices,
                    $selectedMaterials,
                );

                $carBreakdowns[$carId]['package_covered_amount'] = $coverage['package_covered_amount'];
                $carBreakdowns[$carId]['cash_due_amount'] = $coverage['cash_due_amount'];
                $carBreakdowns[$carId]['sub_services_coverage'] = $coverage['sub_services'];
            }

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
            'user_package_id' => $userPackage?->id,
        ], now()->addMinutes(self::QUOTE_TTL_MINUTES));

        return [
            'quote_token' => $token,
            'branch' => $branch,
            'distance_km' => $data['distance_km'] ?? null,
            'sub_services' => $selectedSubServices->values(),
            'materials' => $selectedMaterials,
            'user_package' => $userPackage,
            'cars' => collect($carIds)->map(fn (int $carId) => [
                'car_id' => $carId,
                'price_items' => $carBreakdowns[$carId]['items'],
                'service_price' => $carBreakdowns[$carId]['service_price'],
                'sub_service_price' => $carBreakdowns[$carId]['sub_service_price'],
                'materials_price' => $carBreakdowns[$carId]['materials_price'],
                'total_price' => $carBreakdowns[$carId]['total_price'],
                'package_covered_amount' => $carBreakdowns[$carId]['package_covered_amount'] ?? null,
                'cash_due_amount' => $carBreakdowns[$carId]['cash_due_amount'] ?? null,
                'sub_services_coverage' => $carBreakdowns[$carId]['sub_services_coverage'] ?? null,
            ])->all(),
            'car_count' => count($carIds),
            'total_price' => $totalForGroup,
            'cash_due_total' => $userPackage
                ? round((float) array_sum(array_column($carBreakdowns, 'cash_due_amount')), 2)
                : null,
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
     * match or is duplicated. Materials are stocked per branch, so a branch
     * must be known to check availability against, and each requested
     * quantity is checked against that branch's Inventory.
     */
    protected function resolveMaterials(?Branch $branch, array $materialsInput): array
    {
        if ($materialsInput === []) {
            return [];
        }

        if (! $branch) {
            throw ValidationException::withMessages([
                'branch_id' => [__('Select a branch to book materials.')],
            ]);
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

        $stockByMaterialId = Inventory::where('branch_id', $branch->id)
            ->whereIn('material_id', $materialIds)
            ->pluck('quantity', 'material_id');

        return collect($materialsInput)->map(function (array $row) use ($materials, $stockByMaterialId) {
            $material = $materials->get((int) $row['material_id']);

            if (! $material->is_active || ! $material->is_visible_to_customer) {
                throw ValidationException::withMessages([
                    'materials' => [__('One or more selected materials are not available for booking.')],
                ]);
            }

            $quantity = (float) $row['quantity'];
            $availableQuantity = (float) ($stockByMaterialId->get($material->id) ?? 0);

            if ($availableQuantity < $quantity) {
                throw ValidationException::withMessages([
                    'materials' => [__('Not enough stock available for :material at this branch.', ['material' => $material->name])],
                ]);
            }

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
        $context = [
            'service' => $service,
            'car_ids' => $payload['car_ids'],
            'total_amount_for_group' => $totalAmountForGroup,
            'user_package_id' => $payload['user_package_id'] ?? null,
        ];

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
                    'user_package_id' => $payload['user_package_id'] ?? null,
                    'package_covered_amount' => $carBreakdown['package_covered_amount'] ?? 0,
                    'cash_due_amount' => $carBreakdown['cash_due_amount'] ?? 0,
                    'status' => OrderStatus::PENDING->value,
                ]);

                $order = $this->orderRepository->create($dto);
                $order->priceItems()->createMany($carBreakdown['items']);

                if (! empty($payload['sub_services'])) {
                    $subServiceCoverage = collect($carBreakdown['sub_services_coverage'] ?? [])->keyBy('sub_service_id');

                    $order->subServices()->createMany(collect($payload['sub_services'])->map(fn (array $subService) => [
                        'sub_service_id' => $subService['sub_service_id'],
                        'price' => $subService['price'],
                        'covered_by_package' => (bool) ($subServiceCoverage->get($subService['sub_service_id'])['covered'] ?? false),
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

                $carContext = [
                    ...$context,
                    'package_covered_amount' => $carBreakdown['package_covered_amount'] ?? null,
                    'cash_due_amount' => $carBreakdown['cash_due_amount'] ?? null,
                ];
                $paymentHandler->settle($order, $carBreakdown['total_price'], $carContext);

                $orders[] = $order->load(['priceItems', 'payments', 'subServices.subService', 'materials.material', 'userPackage.package']);
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
     * Decides which branch the booking is served from and the distance the
     * customer is from it. The customer's explicit branch_id wins; otherwise,
     * when we know the customer's location, we pick the nearest active branch
     * for them. distance_km is always derived server-side from the customer's
     * location and the chosen branch — it is never trusted from the client.
     *
     * @return array{0: ?Branch, 1: array}
     */
    protected function resolveBranchAndDistance(array $data): array
    {
        $lat = isset($data['location_lat']) ? (float) $data['location_lat'] : null;
        $lng = isset($data['location_lng']) ? (float) $data['location_lng'] : null;

        if (! empty($data['branch_id'])) {
            $branch = $this->branchRepository->findByIdOrNull((int) $data['branch_id']);
        } elseif ($lat !== null && $lng !== null) {
            $branch = $this->branchRepository->nearest($lat, $lng);
            $data['branch_id'] = $branch?->id;
        } else {
            $branch = null;
        }

        $data['distance_km'] = ($branch && $lat !== null && $lng !== null
            && $branch->latitude !== null && $branch->longitude !== null)
            ? GeoDistance::km($lat, $lng, (float) $branch->latitude, (float) $branch->longitude)
            : null;

        return [$branch, $data];
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
}
