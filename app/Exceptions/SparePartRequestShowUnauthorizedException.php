<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a user tries to view a spare part request he is not allowed to
 * see. Only a super admin, the admin of the owning branch, the customer of the
 * owning order, or the mechanic who raised it, may view it.
 */
class SparePartRequestShowUnauthorizedException extends ApiException
{
    protected string $errorType = 'SPARE_PART_REQUEST_SHOW_UNAUTHORIZED';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_403_FORBIDDEN;

    public function getDefaultMessage(): string
    {
        return __('You are not authorized to view this spare part request.');
    }
}