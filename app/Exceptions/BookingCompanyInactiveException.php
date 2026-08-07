<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a company customer requests a booking but their company
 * account has been deactivated.
 */
class BookingCompanyInactiveException extends ApiException
{
    protected string $errorType = 'BOOKING_COMPANY_INACTIVE';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_403_FORBIDDEN;

    public function getDefaultMessage(): string
    {
        return __('Your company account is currently inactive.');
    }
}
