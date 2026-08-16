<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

/**
 * Thrown when a user exceeds the allowed number of failed login attempts and
 * must wait before trying again.
 */
class TooManyLoginAttemptsException extends ApiException
{
    protected string $errorType = 'TOO_MANY_LOGIN_ATTEMPTS';

    protected int $httpStatusCode = HttpStatusConstants::HTTP_429_TOO_MANY_REQUESTS;

    public function __construct(protected int $secondsRemaining = 0)
    {
        parent::__construct();
    }

    public function getDefaultMessage(): string
    {
        return __('Too many failed login attempts. Please try again in :seconds seconds.', [
            'seconds' => $this->secondsRemaining,
        ]);
    }
}
