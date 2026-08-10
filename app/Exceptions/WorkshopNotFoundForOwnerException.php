<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a workshop-role user with no workshop of their own (or one
 * belonging to someone else) tries to use a workshop-scoped endpoint.
 */
class WorkshopNotFoundForOwnerException extends ApiException
{
    protected string $errorType = 'WORKSHOP_NOT_FOUND_FOR_OWNER';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_404_NOT_FOUND;

    public function getDefaultMessage(): string
    {
        return __('Workshop not found');
    }
}