<?php

namespace App\Exceptions;

use App\Constants\HttpStatusConstants;

class OtpCooldownException extends ApiException
{
    /**
     * Machine-readable error code
     */
    protected string $errorType = 'OTP_COOLDOWN';

    /**
     * HTTP status code for too many requests
     */
    protected int $httpStatusCode = HttpStatusConstants::HTTP_429_TOO_MANY_REQUESTS;

    /**
     * Default error message
     */
    public function getDefaultMessage(): string
    {
        return 'Please wait before resending OTP.';
    }

    /**
     * Constructor with cooldown information
     */
    public function __construct(int $cooldownRemaining)
    {
        parent::__construct(
            message: $this->getDefaultMessage(),
            errors: [
                'cooldown_remaining' => $cooldownRemaining,
                'can_resend' => false,
            ]
        );
    }

    /**
     * Get cooldown remaining in seconds
     */
    public function getCooldownRemaining(): int
    {
        return $this->errors['cooldown_remaining'] ?? 0;
    }

    /**
     * Check if OTP can be resent
     */
    public function canResend(): bool
    {
        return $this->errors['can_resend'] ?? false;
    }
}
