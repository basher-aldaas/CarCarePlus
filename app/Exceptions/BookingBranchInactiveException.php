<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a booking is quoted against a branch that is not active.
 */
class BookingBranchInactiveException extends ApiException
{
    protected string $errorType = 'BOOKING_BRANCH_INACTIVE';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_403_FORBIDDEN;

    public function getDefaultMessage(): string
    {
        return __('This branch is currently inactive and not accepting bookings.');
    }
}
