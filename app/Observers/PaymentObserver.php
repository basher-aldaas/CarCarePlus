<?php

namespace App\Observers;

use App\Enums\PaymentEnums\PaymentStatus;
use App\Enums\PaymentEnums\PaymentType;
use App\Enums\PointsTransactionType;
use App\Models\Payment;
use App\Models\PointsConfig;
use App\Models\PointsTransaction;
use App\Repositories\Eloquent\PointRepository;

class PaymentObserver
{
    public function __construct(protected PointRepository $pointRepository)
    {}

    public function created(Payment $payment): void
    {
        $this->awardPoints($payment);
    }

    public function updated(Payment $payment): void
    {
        if ($payment->wasChanged('status')) {
            $this->awardPoints($payment);
        }
    }

    /**
     * Awards loyalty points once per order, regardless of which payment
     * method settled it, the moment its payment is PAID.
     */
    protected function awardPoints(Payment $payment): void
    {
        if ($payment->status !== PaymentStatus::PAID || $payment->type !== PaymentType::ORDER) {
            return;
        }

        $alreadyEarned = PointsTransaction::where('reference_type', 'order')
            ->where('reference_id', $payment->order_id)
            ->where('type', PointsTransactionType::EARN)
            ->exists();

        if ($alreadyEarned) {
            return;
        }

        $config = PointsConfig::where('is_active', true)->first();

        if (! $config) {
            return;
        }

        $earned = (int) floor((float) $payment->amount * (float) $config->earn_per_amount);
        $earned = min($earned, $config->max_earn_per_order);

        if ($earned <= 0) {
            return;
        }

        $this->pointRepository->createTransaction(
            customer_id: $payment->user_id,
            type: PointsTransactionType::EARN,
            points: $earned,
            reference_type: 'order',
            reference_id: $payment->order_id,
            note: __('Booking Earn #:id', ['id' => $payment->order_id]),
        );
    }
}