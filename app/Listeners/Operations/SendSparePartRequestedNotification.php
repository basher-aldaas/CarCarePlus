<?php

namespace App\Listeners\Operations;

use App\Events\Operations\SparePartRequestCreated;
use App\Notifications\Operations\SparePartRequestedNotification;

class SendSparePartRequestedNotification
{
    public function handle(SparePartRequestCreated $event): void
    {
        $sparePartRequest = $event->sparePartRequest;
        $sparePartRequest->loadMissing('order.customer');

        $sparePartRequest->order?->customer?->notify(
            new SparePartRequestedNotification($sparePartRequest)
        );
    }
}