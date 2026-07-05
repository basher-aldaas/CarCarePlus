<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown on login when the email/password combination does not match.
 */
class InvalidCredentialsException extends ApiException
{
    protected string $errorType = 'INVALID_CREDENTIALS';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_401_UNAUTHORIZED;

    public function getDefaultMessage(): string
    {
        return __('The provided credentials are incorrect.');
    }
}
