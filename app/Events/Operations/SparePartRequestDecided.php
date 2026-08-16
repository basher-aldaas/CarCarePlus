<?php

namespace App\Events\Operations;

use App\Models\SparePartRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a customer approves or rejects a spare-part change request.
 */
class SparePartRequestDecided
{
    use Dispatchable, SerializesModels;

    public function __construct(public SparePartRequest $sparePartRequest) {}
}