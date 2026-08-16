<?php

namespace App\Notifications\Operations;

use App\Enums\NotificationType;
use App\Models\Rating;

/**
 * Sent when a customer rates a completed order — to the assigned employee and
 * to the workshop owner.
 */
class RatingSubmittedNotification extends OperationNotification
{
    public function __construct(public Rating $rating) {}

    protected function type(): NotificationType
    {
        return NotificationType::INFO;
    }

    protected function referenceType(): ?string
    {
        return 'rating';
    }

    protected function referenceId(): ?int
    {
        return $this->rating->id;
    }

    protected function title(object $notifiable): string
    {
        return __('New rating received');
    }

    protected function body(object $notifiable): string
    {
        return __('A customer left a :stars-star rating on booking #:order.', [
            'stars' => $this->rating->service_rating,
            'order' => $this->rating->order_id,
        ]);
    }
}