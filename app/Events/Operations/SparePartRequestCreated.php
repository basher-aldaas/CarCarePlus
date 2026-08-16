<?php

namespace App\Events\Operations;

use App\Models\SparePartRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a technician raises a spare-part change request on an order.
 */
class SparePartRequestCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public SparePartRequest $sparePartRequest) {}
}