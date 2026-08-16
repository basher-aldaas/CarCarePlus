<?php

namespace App\Events\Operations;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired by the scheduler for each order whose scheduled time is approaching
 * and that still needs its customer / employee reminder.
 */
class OrderReminderDue
{
    use Dispatchable, SerializesModels;

    /**
     * @param 'customer'|'employee' $audience
     */
    public function __construct(
        public Order $order,
        public string $audience,
    ) {}
}