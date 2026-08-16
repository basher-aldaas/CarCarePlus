<?php

namespace App\Listeners\Operations;

use App\Enums\OrderEnums\OrderStatus;
use App\Events\Operations\BookingStatusChanged;
use App\Notifications\Operations\BookingStatusNotification;

class SendBookingStatusNotification
{
    public function handle(BookingStatusChanged $event): void
    {
        $order = $event->order;
        $order->loadMissing('customer', 'employee.user');

        $customer = $order->customer;
        $employeeUser = $order->employee?->user;

        switch ($event->status) {
            case OrderStatus::ASSIGNED:
                $customer?->notify(new BookingStatusNotification($order, $event->status, 'customer'));
                $employeeUser?->notify(new BookingStatusNotification($order, $event->status, 'employee'));
                break;

            case OrderStatus::IN_PROGRESS:
            case OrderStatus::COMPLETED:
                $customer?->notify(new BookingStatusNotification($order, $event->status, 'customer'));
                break;

            case OrderStatus::CANCELLED:
                $customer?->notify(new BookingStatusNotification($order, $event->status, 'customer', $event->reason));
                $employeeUser?->notify(new BookingStatusNotification($order, $event->status, 'employee', $event->reason));
                break;

            default:
                // Other transitions don't notify anyone.
                break;
        }
    }
}