<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Enums\OrderEnums\OrderStatus;
use App\Models\Notification;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendScheduledOrderReminders extends Command
{
    protected $signature = 'orders:send-reminders';

    protected $description = 'Remind the customer 30 minutes before, and the assigned employee 1 hour before, a scheduled order.';

    private const CUSTOMER_REMINDER = 'order_customer_reminder';
    private const EMPLOYEE_REMINDER = 'order_employee_reminder';

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
            Notification::create([
                'user_id' => $order->customer_id,
                'title' => __('Upcoming appointment reminder'),
                'body' => __('Your appointment is scheduled at :time (in about 30 minutes).', [
                    'time' => $order->scheduled_at->format('Y-m-d H:i'),
                ]),
                'type' => NotificationType::INFO->value,
                'reference_type' => self::CUSTOMER_REMINDER,
                'reference_id' => $order->id,
                'is_read' => false,
            ]);
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
            $employeeUserId = $order->employee?->user_id;

            if ($employeeUserId === null) {
                continue;
            }

            Notification::create([
                'user_id' => $employeeUserId,
                'title' => __('Upcoming job reminder'),
                'body' => __('You have a job scheduled at :time (in about 1 hour).', [
                    'time' => $order->scheduled_at->format('Y-m-d H:i'),
                ]),
                'type' => NotificationType::INFO->value,
                'reference_type' => self::EMPLOYEE_REMINDER,
                'reference_id' => $order->id,
                'is_read' => false,
            ]);
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
