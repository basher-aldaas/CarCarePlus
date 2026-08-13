<?php

namespace App\Services\Operations\Payment;

use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Enums\PaymentEnums\PaymentType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Str;

class CashPaymentHandler implements PaymentMethodHandlerInterface
{
    public function validate(User $customer, float $totalAmountForGroup, array $context): void
    {
        // Nothing to check upfront — cash is settled with staff on delivery.
    }

    public function settle(Order $order, float $amount, array $context): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->customer_id,
            'payment_number' => (string) Str::uuid(),
            'type' => PaymentType::ORDER,
            'method' => PaymentMethod::CASH,
            'status' => PaymentStatus::PENDING,
            'amount' => $amount,
        ]);
    }

    /**
     * Cash is only collected from the customer on delivery, so a cancelled
     * cash payment never took any money — just void the pending record.
     */
    public function refund(Order $order, Payment $payment): void
    {
        if ($payment->status === PaymentStatus::REFUNDED) {
            return;
        }

        $payment->update(['status' => PaymentStatus::REFUNDED]);
    }
}
