<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when an OTP code is invalid, expired, already used, or has exceeded
 * the maximum number of verification attempts.
 */
class InvalidOtpException extends ApiException
{
    protected string $errorType = 'INVALID_OTP';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_422_UNPROCESSABLE_ENTITY;

    public function getDefaultMessage(): string
    {
        return __('The verification code is invalid or has expired.');
    }
}
