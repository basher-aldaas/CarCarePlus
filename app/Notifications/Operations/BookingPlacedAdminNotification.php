<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Models\Order;

/**
 * Sent to the branch admin when a customer confirms a new booking in their
 * branch, so they can assign a technician.
 */
class BookingPlacedAdminNotification extends OperationNotification
{
    public function __construct(
        public Order $order,
        public string $customerName,
        public int $count = 1,
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
        return __('New booking received');
    }

    protected function body(object $notifiable): string
    {
        if ($this->count > 1) {
            return __(':customer confirmed a new booking of :count services (starting with #:id). Please assign a technician.', [
                'customer' => $this->customerName,
                'count' => $this->count,
                'id' => $this->order->id,
            ]);
        }

        return __(':customer confirmed a new booking #:id. Please assign a technician.', [
            'customer' => $this->customerName,
            'id' => $this->order->id,
        ]);
    }
}
