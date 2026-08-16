<?php

namespace App\Listeners\Operations;

use App\Events\Operations\SparePartRequestDecided;
use App\Notifications\Operations\SparePartDecidedNotification;

class SendSparePartDecidedNotification
{
    public function handle(SparePartRequestDecided $event): void
    {
        $sparePartRequest = $event->sparePartRequest;
        $sparePartRequest->loadMissing('employee.user');

        $sparePartRequest->employee?->user?->notify(
            new SparePartDecidedNotification($sparePartRequest)
        );
    }
}