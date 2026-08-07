<?php

namespace App\Services\Operations;

use App\DTOs\OrderDTO;
use App\DTOs\PriceBreakdownDTO;
use App\Enums\OrderEnums\OrderStatus;
use App\Enums\PaymentEnums\PaymentMethod;
use App\Exceptions\BookingBranchInactiveException;
use App\Exceptions\BookingCompanyInactiveException;
use App\Models\Car;
use App\Models\Company;
use App\Models\User;
use App\Repositories\Eloquent\BranchRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\ServiceRepository;
use App\Services\Operations\Booking\BookingTypeHandlerResolver;
use App\Services\Operations\Payment\PaymentMethodHandlerResolver;
use Carbon\Carbon;
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

            $carBreakdowns[$carId] = $breakdown;
            $totalForGroup += $breakdown->totalPrice;
        }

        $totalForGroup = round($totalForGroup, 2);

        $paymentHandler->validate($customer, $totalForGroup, $context);

        $token = (string) Str::uuid();

        Cache::put($token, [
            'customer_id' => $customer->id,
            'car_ids' => $carIds,
            'data' => $data,
            'car_breakdowns' => collect($carBreakdowns)->map(fn (PriceBreakdownDTO $breakdown) => [
                'items' => $breakdown->items,
                'discount_amount' => $breakdown->discountAmount,
                'total_price' => $breakdown->totalPrice,
            ])->all(),
            'payment_method' => $paymentMethod->value,
        ], now()->addMinutes(self::QUOTE_TTL_MINUTES));

        return [
            'quote_token' => $token,
            'branch' => $branch,
            'cars' => collect($carIds)->map(fn (int $carId) => [
                'car_id' => $carId,
                'price_items' => $carBreakdowns[$carId]->items,
                'total_price' => $carBreakdowns[$carId]->totalPrice,
            ])->all(),
            'car_count' => count($carIds),
            'total_price' => $totalForGroup,
            'expires_at' => now()->addMinutes(self::QUOTE_TTL_MINUTES)->toIso8601String(),
        ];
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
        $totalAmountForGroup = round(array_sum(array_column($payload['car_breakdowns'], 'total_price')), 2);
        $context = ['service' => $service, 'car_ids' => $payload['car_ids'], 'total_amount_for_group' => $totalAmountForGroup];

        $customer = User::findOrFail($payload['customer_id']);
        $companyId = $this->resolveCompany($customer)?->id;

        return DB::transaction(function () use ($payload, $data, $bookingTypeHandler, $paymentHandler, $context, $companyId) {
            $groupId = $companyId !== null ? (string) Str::uuid() : null;
            $orders = [];

            foreach ($payload['car_ids'] as $carId) {
                $carBreakdown = $payload['car_breakdowns'][$carId];

                $dto = OrderDTO::fromArray([
                    ...$data,
                    'booking_group_id' => $groupId,
                    'customer_id' => $payload['customer_id'],
                    'company_id' => $companyId,
                    'car_id' => $carId,
                    'discount_amount' => $carBreakdown['discount_amount'],
                    'total_price' => $carBreakdown['total_price'],
                    'status' => OrderStatus::PENDING->value,
                ]);

                $order = $this->orderRepository->create($dto);
                $order->priceItems()->createMany($carBreakdown['items']);
                $bookingTypeHandler->afterCreate($order, $data);
                $paymentHandler->settle($order, $carBreakdown['total_price'], $context);

                $orders[] = $order->load(['priceItems', 'payments']);
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
            totalPrice: 0.0,
        );
    }
}
