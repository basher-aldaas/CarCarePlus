<?php

namespace App\Events\Operations;

use App\Enums\OrderEnums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired whenever a booking transitions to a new status (assigned, started,
 * completed, cancelled).
 */
class BookingStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public OrderStatus $status,
        public ?string $reason = null,
    ) {}
}