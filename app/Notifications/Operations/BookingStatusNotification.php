<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Enums\OrderEnums\OrderStatus;
use App\Models\Order;

/**
 * Sent when a booking changes state (assigned / started / completed /
 * cancelled). The wording is tailored to the recipient — the customer who
 * owns the booking, or the employee assigned to it.
 */
class BookingStatusNotification extends OperationNotification
{
    /**
     * @param 'customer'|'employee' $audience
     */
    public function __construct(
        public Order $order,
        public OrderStatus $status,
        public string $audience,
        public ?string $reason = null,
    ) {}

    protected function type(): NotificationType
    {
        return match ($this->status) {
            OrderStatus::COMPLETED => NotificationType::SUCCESS,
            OrderStatus::CANCELLED => NotificationType::WARNING,
            default => NotificationType::INFO,
        };
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
        return match ([$this->status, $this->audience]) {
            [OrderStatus::ASSIGNED, 'customer'] => __('Your booking has been assigned'),
            [OrderStatus::ASSIGNED, 'employee'] => __('New job assigned to you'),
            [OrderStatus::IN_PROGRESS, 'customer'] => __('Your service has started'),
            [OrderStatus::COMPLETED, 'customer'] => __('Your service is complete'),
            [OrderStatus::CANCELLED, 'customer'] => __('Your booking was cancelled'),
            [OrderStatus::CANCELLED, 'employee'] => __('A job was cancelled'),
            default => __('Booking update'),
        };
    }

    protected function body(object $notifiable): string
    {
        $id = $this->order->id;

        $message = match ([$this->status, $this->audience]) {
            [OrderStatus::ASSIGNED, 'customer'] => __('Your booking #:id has been assigned to a technician and will be handled soon.', ['id' => $id]),
            [OrderStatus::ASSIGNED, 'employee'] => __('You have been assigned to booking #:id. Please review its details.', ['id' => $id]),
            [OrderStatus::IN_PROGRESS, 'customer'] => __('Work on your booking #:id has started.', ['id' => $id]),
            [OrderStatus::COMPLETED, 'customer'] => __('Your booking #:id has been completed. We would love your feedback!', ['id' => $id]),
            [OrderStatus::CANCELLED, 'customer'] => __('Your booking #:id has been cancelled.', ['id' => $id]),
            [OrderStatus::CANCELLED, 'employee'] => __('Booking #:id assigned to you has been cancelled.', ['id' => $id]),
            default => __('Booking #:id has been updated.', ['id' => $id]),
        };

        if ($this->status === OrderStatus::CANCELLED && $this->reason) {
            $message .= ' ' . __('Reason: :reason', ['reason' => $this->reason]);
        }

        return $message;
    }
}