<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a booking status change is requested that isn't valid from the
 * booking's current status (e.g. cancelling an already completed booking).
 */
class InvalidBookingStatusTransitionException extends ApiException
{
    protected string $errorType = 'INVALID_BOOKING_STATUS_TRANSITION';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_409_CONFLICT;

    public function getDefaultMessage(): string
    {
        return __('This action cannot be performed on the booking in its current status.');
    }
}