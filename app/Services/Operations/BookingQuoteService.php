<?php

namespace App\Services\Operations;

use App\DTOs\OrderDTO;
use App\DTOs\PriceBreakdownDTO;
use App\Enums\OrderEnums\OrderStatus;
use App\Enums\PaymentEnums\PaymentMethod;
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
 * that token and creates the real order(s) — one per car — sharing a
 * booking_group_id.
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
        $carIds = $this->resolveCarIds($customer, $data);

        $bookingTypeHandler = $this->bookingTypeHandlerResolver->resolve($data);
        $bookingTypeHandler->validate($data);

        $service = $this->serviceRepository->findById((int) $data['service_id']);
        $branch = isset($data['branch_id']) ? $this->branchRepository->findByIdOrNull((int) $data['branch_id']) : null;
        $scheduledAt = isset($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : now();

        $paymentMethod = PaymentMethod::from($data['payment_method']);
        $paymentHandler = $this->paymentMethodHandlerResolver->resolve($paymentMethod);

        $context = ['service' => $service, 'car_ids' => $carIds];

        $breakdown = $paymentHandler->pricingIsSkipped()
            ? $this->coveredByPackageBreakdown()
            : $this->pricingEngine->calculate(
                service: $service,
                isVip: (bool) ($data['is_vip'] ?? false),
                distanceKm: isset($data['distance_km']) ? (float) $data['distance_km'] : null,
                branch: $branch,
                scheduledAt: $scheduledAt,
            );

        $totalForGroup = round($breakdown->totalPrice * count($carIds), 2);

        $paymentHandler->validate($customer, $totalForGroup, $context);

        $token = (string) Str::uuid();

        Cache::put($token, [
            'customer_id' => $customer->id,
            'car_ids' => $carIds,
            'data' => $data,
            'breakdown_items' => $breakdown->items,
            'discount_amount' => $breakdown->discountAmount,
            'total_price_per_car' => $breakdown->totalPrice,
            'payment_method' => $paymentMethod->value,
        ], now()->addMinutes(self::QUOTE_TTL_MINUTES));

        return [
            'quote_token' => $token,
            'branch' => $branch,
            'price_items' => $breakdown->items,
            'car_count' => count($carIds),
            'total_price_per_car' => $breakdown->totalPrice,
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
        $context = ['service' => $service, 'car_ids' => $payload['car_ids']];

        $customer = User::findOrFail($payload['customer_id']);
        $companyId = $customer->hasRole('customer_company')
            ? Company::where('customer_id', $customer->id)->value('id')
            : null;

        return DB::transaction(function () use ($payload, $data, $bookingTypeHandler, $paymentHandler, $context, $companyId) {
            $groupId = (string) Str::uuid();
            $orders = [];

            foreach ($payload['car_ids'] as $carId) {
                $dto = OrderDTO::fromArray([
                    ...$data,
                    'booking_group_id' => $groupId,
                    'customer_id' => $payload['customer_id'],
                    'company_id' => $companyId,
                    'car_id' => $carId,
                    'discount_amount' => $payload['discount_amount'],
                    'total_price' => $payload['total_price_per_car'],
                    'status' => OrderStatus::PENDING->value,
                ]);

                $order = $this->orderRepository->create($dto);
                $order->priceItems()->createMany($payload['breakdown_items']);
                $bookingTypeHandler->afterCreate($order, $data);
                $paymentHandler->settle($order, $payload['total_price_per_car'], $context);

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

    protected function coveredByPackageBreakdown(): PriceBreakdownDTO
    {
        return new PriceBreakdownDTO(
            items: [['pricing_rule_id' => null, 'label' => 'Covered by Package', 'amount' => 0.0]],
            discountAmount: 0.0,
            totalPrice: 0.0,
        );
    }
}
