<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a user tries to cancel a booking they do not own. Only the
 * booking's customer or a super admin/admin may cancel it.
 */
class BookingCancelUnauthorizedException extends ApiException
{
    protected string $errorType = 'BOOKING_CANCEL_UNAUTHORIZED';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_403_FORBIDDEN;

    public function getDefaultMessage(): string
    {
        return __('You are not allowed to cancel this booking.');
    }
}