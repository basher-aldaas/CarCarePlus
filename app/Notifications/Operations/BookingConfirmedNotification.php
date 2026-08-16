<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Models\Order;

/**
 * Sent to the customer when their booking is confirmed. A single confirmation
 * may cover several cars (one order each), so the count is included.
 */
class BookingConfirmedNotification extends OperationNotification
{
    public function __construct(
        public Order $order,
        public int $count = 1,
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
        return __('Your booking has been confirmed');
    }

    protected function body(object $notifiable): string
    {
        if ($this->count > 1) {
            return __('Your booking of :count services has been confirmed. We will assign a technician shortly.', [
                'count' => $this->count,
            ]);
        }

        return __('Your booking #:id has been confirmed. We will assign a technician shortly.', [
            'id' => $this->order->id,
        ]);
    }
}
