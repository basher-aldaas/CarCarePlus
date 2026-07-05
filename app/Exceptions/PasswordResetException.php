<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a password reset cannot be completed — e.g. an invalid or
 * expired token, or an unknown email.
 */
class PasswordResetException extends ApiException
{
    protected string $errorType = 'PASSWORD_RESET_FAILED';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_400_BAD_REQUEST;

    public function getDefaultMessage(): string
    {
        return __('Unable to reset the password. The link may be invalid or expired.');
    }
}
