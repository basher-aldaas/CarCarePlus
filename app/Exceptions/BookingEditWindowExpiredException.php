<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a booking is edited less than 2 hours before its scheduled
 * time (or, for immediate bookings, at any point after creation).
 */
class BookingEditWindowExpiredException extends ApiException
{
    protected string $errorType = 'BOOKING_EDIT_WINDOW_EXPIRED';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_422_UNPROCESSABLE_ENTITY;

    public function getDefaultMessage(): string
    {
        return __('This booking can no longer be edited; it is less than 2 hours away.');
    }
}
