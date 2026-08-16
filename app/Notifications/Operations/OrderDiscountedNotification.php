<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Models\Order;

/**
 * Sent to the customer when a super admin / admin grants a discount on their
 * pending booking, telling them how much was taken off and the new total.
 */
class OrderDiscountedNotification extends OperationNotification
{
    public function __construct(
        public Order $order,
        public float $discountAmount,
        public ?string $reason = null,
    ) {}

    protected function type(): NotificationType
    {
        return NotificationType::SUCCESS;
    }

    protected function referenceType(): ?string
    {
        return 'order';
    }

    protected function referenceId(): ?int
    {
        return $this->order->id;
    }

    protected function title(object $notifiable): string
    {
        return __('A discount was applied to your booking');
    }

    protected function body(object $notifiable): string
    {
        $message = __('Good news! A discount of :amount was applied to your booking #:id. Your new total is :total.', [
            'amount' => number_format($this->discountAmount, 2),
            'id' => $this->order->id,
            'total' => number_format((float) $this->order->total_price, 2),
        ]);

        if ($this->reason) {
            $message .= ' ' . __('Reason: :reason', ['reason' => $this->reason]);
        }

        return $message;
    }
}
