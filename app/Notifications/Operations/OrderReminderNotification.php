<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Models\Order;

/**
 * Reminder sent ahead of a scheduled order — 30 minutes before to the customer,
 * 1 hour before to the assigned employee.
 *
 * The reference type doubles as the "already reminded" marker the scheduler
 * checks against, so a reminder is only ever sent once per order/audience.
 */
class OrderReminderNotification extends OperationNotification
{
    public const CUSTOMER_MARKER = 'order_customer_reminder';
    public const EMPLOYEE_MARKER = 'order_employee_reminder';

    /**
     * @param 'customer'|'employee' $audience
     */
    public function __construct(
        public Order $order,
        public string $audience,
    ) {}

    protected function type(): NotificationType
    {
        return NotificationType::INFO;
    }

    protected function referenceType(): ?string
    {
        return $this->audience === 'customer'
            ? self::CUSTOMER_MARKER
            : self::EMPLOYEE_MARKER;
    }

    protected function referenceId(): ?int
    {
        return $this->order->id;
    }

    protected function title(object $notifiable): string
    {
        return $this->audience === 'customer'
            ? __('Upcoming appointment reminder')
            : __('Upcoming job reminder');
    }

    protected function body(object $notifiable): string
    {
        $time = $this->order->scheduled_at?->format('Y-m-d H:i');

        return $this->audience === 'customer'
            ? __('Your appointment is scheduled at :time (in about 30 minutes).', ['time' => $time])
            : __('You have a job scheduled at :time (in about 1 hour).', ['time' => $time]);
    }
}