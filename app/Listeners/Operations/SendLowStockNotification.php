<?php

namespace App\Listeners\Operations;

use App\Events\Operations\LowStockDetected;
use App\Models\User;
use App\Notifications\Operations\LowStockNotification;

class SendLowStockNotification
{
    public function handle(LowStockDetected $event): void
    {
        User::find($event->adminId)?->notify(new LowStockNotification(
            $event->inventoryId,
            $event->materialName,
            $event->quantity,
            $event->min,
        ));
    }
}