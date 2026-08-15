<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a user tries to rebook a booking they do not own. A booking
 * may only be rebooked by the same customer who originally placed it.
 */
class BookingRebookUnauthorizedException extends ApiException
{
    protected string $errorType = 'BOOKING_REBOOK_UNAUTHORIZED';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_403_FORBIDDEN;

    public function getDefaultMessage(): string
    {
        return __('You are not allowed to rebook this booking.');
    }
}