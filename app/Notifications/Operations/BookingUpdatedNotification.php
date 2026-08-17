<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Models\Order;

/**
 * Sent to the customer when their booking is updated (by staff or by an edit
 * that someone else made on their behalf).
 */
class BookingUpdatedNotification extends OperationNotification
{
    public function __construct(public Order $order) {}

    protected function type(): NotificationType
    {
        return NotificationType::INFO;
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
        return __('Your booking was updated');
    }

    protected function body(object $notifiable): string
    {
        return __('Your booking #:id has been updated. Please review the latest details.', [
            'id' => $this->order->id,
        ]);
    }
}
