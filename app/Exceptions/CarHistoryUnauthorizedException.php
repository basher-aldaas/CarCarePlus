<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a workshop tries to view the service history of a car that
 * has never had an order at that workshop.
 */
class CarHistoryUnauthorizedException extends ApiException
{
    protected string $errorType = 'CAR_HISTORY_UNAUTHORIZED';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_403_FORBIDDEN;

    public function getDefaultMessage(): string
    {
        return __('This car has no service history at your workshop.');
    }
}