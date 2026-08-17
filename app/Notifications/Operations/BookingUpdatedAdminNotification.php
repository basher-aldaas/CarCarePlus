<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Models\Order;

/**
 * Sent to the branch admin when a booking in their branch is updated, naming
 * who made the change.
 */
class BookingUpdatedAdminNotification extends OperationNotification
{
    public function __construct(
        public Order $order,
        public string $actorName,
    ) {}

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
        return __('A booking was updated');
    }

    protected function body(object $notifiable): string
    {
        return __('Booking #:id was updated by :actor.', [
            'id' => $this->order->id,
            'actor' => $this->actorName,
        ]);
    }
}
