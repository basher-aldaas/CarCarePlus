<?php

namespace App\Listeners\Operations;

use App\Events\Operations\PurchaseRequestDecided;
use App\Models\Branch;
use App\Models\User;
use App\Notifications\Operations\PurchaseRequestDecidedNotification;

class SendPurchaseRequestDecidedNotification
{
    public function handle(PurchaseRequestDecided $event): void
    {
        $purchaseRequest = $event->purchaseRequest;

        // The admin of the requesting branch owns the request.
        $adminId = Branch::where('id', $purchaseRequest->branch_id)->value('admin_id');

        if ($adminId === null) {
            return;
        }

        User::find($adminId)?->notify(
            new PurchaseRequestDecidedNotification($purchaseRequest)
        );
    }
}