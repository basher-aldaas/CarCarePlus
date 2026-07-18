<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a user tries to delete a car they do not own. Only the car's
 * owner (the customer who created it) or a super admin may delete it.
 */
class CarDeleteUnauthorizedException extends ApiException
{
    protected string $errorType = 'CAR_DELETE_UNAUTHORIZED';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_403_FORBIDDEN;

    public function getDefaultMessage(): string
    {
        return __('You are not allowed to delete this car.');
    }
}
