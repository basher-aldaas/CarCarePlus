<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a user tries to view a booking they do not own. Only the
 * booking's customer or staff who may manage it can view it.
 */
class BookingShowUnauthorizedException extends ApiException
{
    protected string $errorType = 'BOOKING_SHOW_UNAUTHORIZED';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_403_FORBIDDEN;

    public function getDefaultMessage(): string
    {
        return __('You are not allowed to show this booking.');
    }
}