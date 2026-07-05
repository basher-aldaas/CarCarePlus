<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown on login when the account exists but is not active — e.g. a company /
 * workshop still pending super-admin approval, or one that was rejected.
 */
class AccountInactiveException extends ApiException
{
    protected string $errorType = 'ACCOUNT_INACTIVE';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_403_FORBIDDEN;

    public function getDefaultMessage(): string
    {
        return __('Your account is not active. It may be pending approval or has been rejected.');
    }
}
