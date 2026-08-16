<?php

namespace App\Events\Operations;

use App\Models\Rating;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a customer submits a rating for a completed order.
 */
class RatingSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Rating $rating) {}
}