<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a user tries to update a car they do not own. Only the car's
 * owner (the customer who created it) or a super admin may update it.
 */
class CarUpdateUnauthorizedException extends ApiException
{
    protected string $errorType = 'CAR_UPDATE_UNAUTHORIZED';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_403_FORBIDDEN;

    public function getDefaultMessage(): string
    {
        return __('You are not allowed to update this car.');
    }
}
