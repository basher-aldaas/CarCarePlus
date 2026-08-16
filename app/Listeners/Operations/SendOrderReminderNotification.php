<?php

namespace App\Listeners\Operations;

use App\Events\Operations\OrderReminderDue;
use App\Notifications\Operations\OrderReminderNotification;

class SendOrderReminderNotification
{
    public function handle(OrderReminderDue $event): void
    {
        $order = $event->order;

        // Sent synchronously (notifyNow) so the in-app reminder row is written
        // immediately: the scheduler's "already reminded" check relies on it to
        // avoid sending the same reminder again on the next run.
        if ($event->audience === 'customer') {
            $order->loadMissing('customer');
            $order->customer?->notifyNow(new OrderReminderNotification($order, 'customer'));

            return;
        }

        $order->loadMissing('employee.user');
        $order->employee?->user?->notifyNow(new OrderReminderNotification($order, 'employee'));
    }
}