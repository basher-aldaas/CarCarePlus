<?php

namespace App\Listeners\Operations;

use App\Events\Operations\PurchaseRequestSubmitted;
use App\Models\Branch;
use App\Models\User;
use App\Notifications\Operations\PurchaseRequestSubmittedNotification;
use Illuminate\Support\Facades\Notification;

class SendPurchaseRequestSubmittedNotification
{
    public function handle(PurchaseRequestSubmitted $event): void
    {
        $purchaseRequest = $event->purchaseRequest;

        // Every super admin can approve, plus the admin of the requesting branch.
        $recipients = User::role('super_admin')->get();

        $adminId = Branch::where('id', $purchaseRequest->branch_id)->value('admin_id');

        if ($adminId !== null) {
            $admin = User::find($adminId);

            if ($admin !== null && ! $recipients->contains('id', $admin->id)) {
                $recipients->push($admin);
            }
        }

        Notification::send($recipients, new PurchaseRequestSubmittedNotification($purchaseRequest));
    }
}