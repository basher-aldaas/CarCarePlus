<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Models\Payment;

/**
 * Sent to the customer when their cash payment has been confirmed by staff.
 */
class CashPaymentConfirmedNotification extends OperationNotification
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
        return __('Payment confirmed');
    }

    protected function body(object $notifiable): string
    {
        return __('Your cash payment of :amount for booking #:order has been confirmed.', [
            'amount' => $this->payment->amount,
            'order' => $this->payment->order_id,
        ]);
    }
}