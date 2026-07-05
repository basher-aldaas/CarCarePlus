<?php

namespace App\DTOs\AuthDTOs;

class ResetPasswordOtpDTO
{
    public function __construct(
        public string $email,
        public string $otp,
        public string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email:    $data['email'],
            otp:      (string) $data['otp'],
            password: $data['password'],
        );
    }
}
