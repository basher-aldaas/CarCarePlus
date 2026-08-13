<?php

namespace App\Services\Operations\Payment;

use App\Enums\OrderEnums\OrderStatus;
use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Enums\PaymentEnums\PaymentType;
use App\Enums\PointsTransactionType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PointsConfig;
use App\Models\User;
use App\Models\UserPoint;
use App\Repositories\Eloquent\PointRepository;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PointsPaymentHandler implements PaymentMethodHandlerInterface
{
    public function __construct(protected PointRepository $pointRepository)
    {}

    public function validate(User $customer, float $totalAmountForGroup, array $context): void
    {
        $config = $this->activeConfig();
        $pointsNeeded = $this->pointsFor($totalAmountForGroup, $config);

        if ($pointsNeeded < $config->min_redeem) {
            throw ValidationException::withMessages([
                'points' => [__('This order is below the minimum redeemable points amount')],
            ]);
        }

        $balance = UserPoint::where('customer_id', $customer->id)->value('balance') ?? 0;

        if ($balance < $pointsNeeded) {
            throw ValidationException::withMessages([
                'points' => [__('Not Enough points in your balance to complete this order')],
            ]);
        }
    }

    public function settle(Order $order, float $amount, array $context): Payment
    {
        $config = $this->activeConfig();
        $pointsUsed = $this->pointsFor($amount, $config);

        $this->pointRepository->createTransaction(
            customer_id: $order->customer_id,
            type: PointsTransactionType::REDEEM,
            points: $pointsUsed,
            reference_type: 'order',
            reference_id: $order->id,
            note: __('Booking Paid #:id', ['id' => $order->id]),
        );

        return Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->customer_id,
            'payment_number' => (string) Str::uuid(),
            'type' => PaymentType::ORDER,
            'method' => PaymentMethod::POINT,
            'status' => PaymentStatus::PAID,
            'amount' => $amount,
            'points_used' => $pointsUsed,
        ]);
    }

    /**
     * Return the exact points that were redeemed for this payment back to the
     * customer's balance and mark the payment refunded.
     */
    public function refund(Order $order, Payment $payment): void
    {
        if ($payment->status === PaymentStatus::REFUNDED) {
            return;
        }

        $this->pointRepository->createTransaction(
            customer_id: $order->customer_id,
            type: PointsTransactionType::EARN,
            points: (int) $payment->points_used,
            reference_type: 'order',
            reference_id: $order->id,
            note: __('Refund for cancelled booking #:id', ['id' => $order->id]),
        );

        $payment->update(['status' => PaymentStatus::REFUNDED]);
    }

    protected function activeConfig(): PointsConfig
    {
        $config = PointsConfig::where('is_active', true)->first();

        if (! $config) {
            throw ValidationException::withMessages([
                'points' => [__('Points redemption is not currently available')],
            ]);
        }

        return $config;
    }

    protected function pointsFor(float $amount, PointsConfig $config): int
    {
        return (int) ceil($amount / $config->redeem_value);
    }
}
