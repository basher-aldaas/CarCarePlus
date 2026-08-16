<?php

namespace App\Events\Operations;

use App\Models\PurchaseRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a new purchase (or transfer) request is submitted for approval.
 */
class PurchaseRequestSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(public PurchaseRequest $purchaseRequest) {}
}