<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a user tries to update a booking they do not own. Only the
 * booking's customer or a super admin/admin may update it.
 */
class BookingUpdateUnauthorizedException extends ApiException
{
    protected string $errorType = 'BOOKING_UPDATE_UNAUTHORIZED';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_403_FORBIDDEN;

    public function getDefaultMessage(): string
    {
        return __('You are not allowed to update this booking.');
    }
}