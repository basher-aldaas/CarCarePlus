<?php

namespace App\Events\Operations;

use App\Models\PurchaseRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a purchase request is approved or rejected.
 */
class PurchaseRequestDecided
{
    use Dispatchable, SerializesModels;

    public function __construct(public PurchaseRequest $purchaseRequest) {}
}