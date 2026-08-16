<?php

namespace App\Console\Commands;

use App\Enums\OrderEnums\OrderStatus;
use App\Events\Operations\OrderReminderDue;
use App\Models\Order;
use App\Notifications\Operations\OrderReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendScheduledOrderReminders extends Command
{
    protected $signature = 'orders:send-reminders';

    protected $description = 'Remind the customer 30 minutes before, and the assigned employee 1 hour before, a scheduled order.';

    private const CUSTOMER_REMINDER = OrderReminderNotification::CUSTOMER_MARKER;
    private const EMPLOYEE_REMINDER = OrderReminderNotification::EMPLOYEE_MARKER;

    /** Orders still ahead of their appointment that may need reminding. */
    private const UPCOMING_STATUSES = [
        OrderStatus::PENDING->value,
        OrderStatus::ASSIGNED->value,
    ];

    public function handle(): int
    {
        $now = now();

        $this->remindCustomers($now);
        $this->remindEmployees($now);

        return self::SUCCESS;
    }

    /**
     * Notify the customer for every order whose appointment is within the next
     * 30 minutes and hasn't been reminded yet.
     */
    private function remindCustomers(Carbon $now): void
    {
        $orders = $this->dueOrders($now, minutes: 30, marker: self::CUSTOMER_REMINDER);

        foreach ($orders as $order) {
            OrderReminderDue::dispatch($order, 'customer');
        }
    }

    /**
     * Notify the assigned employee for every order whose appointment is within
     * the next hour and hasn't been reminded yet.
     */
    private function remindEmployees(Carbon $now): void
    {
        $orders = $this->dueOrders($now, minutes: 60, marker: self::EMPLOYEE_REMINDER)
            ->whereNotNull('employee_id');

        $orders->load('employee');

        foreach ($orders as $order) {
            if ($order->employee?->user_id === null) {
                continue;
            }

            OrderReminderDue::dispatch($order, 'employee');
        }
    }

    /**
     * Upcoming orders whose appointment falls inside the next $minutes window
     * and that have not already received the $marker reminder.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Order>
     */
    private function dueOrders(Carbon $now, int $minutes, string $marker)
    {
        return Order::query()
            ->whereNotNull('scheduled_at')
            ->whereIn('status', self::UPCOMING_STATUSES)
            ->where('scheduled_at', '>', $now)
            ->where('scheduled_at', '<=', $now->copy()->addMinutes($minutes))
            ->whereNotExists(function ($query) use ($marker) {
                $query->selectRaw('1')
                    ->from('notifications')
                    ->whereColumn('notifications.reference_id', 'orders.id')
                    ->where('notifications.reference_type', $marker);
            })
            ->get();
    }
}
