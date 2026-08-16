<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Models\Payment;

/**
 * Sent to the branch admin when a cash payment is confirmed for one of their
 * branch's bookings, so they have a record of the collected cash.
 */
class CashPaymentConfirmedAdminNotification extends OperationNotification
{
    public function __construct(public Payment $payment) {}

    protected function type(): NotificationType
    {
        return NotificationType::SUCCESS;
    }

    protected function referenceType(): ?string
    {
        return 'payment';
    }

    protected function referenceId(): ?int
    {
        return $this->payment->id;
    }

    protected function title(object $notifiable): string
    {
        return __('Cash payment confirmed');
    }

    protected function body(object $notifiable): string
    {
        return __('A cash payment of :amount for booking #:order has been confirmed.', [
            'amount' => number_format((float) $this->payment->amount, 2),
            'order' => $this->payment->order_id,
        ]);
    }
}
