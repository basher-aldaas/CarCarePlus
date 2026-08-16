<?php

namespace App\Listeners\Operations;

use App\Events\Operations\RatingSubmitted;
use App\Notifications\Operations\RatingSubmittedNotification;

class SendRatingSubmittedNotification
{
    public function handle(RatingSubmitted $event): void
    {
        $rating = $event->rating;
        $rating->loadMissing('employee.user', 'order.workshop.owner');

        $notification = new RatingSubmittedNotification($rating);

        // The rated employee and the workshop owner both hear about it.
        $rating->employee?->user?->notify($notification);
        $rating->order?->workshop?->owner?->notify($notification);
    }
}