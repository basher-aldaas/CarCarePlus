<?php

namespace App\DTOs\AuhDTOs;

class ForgotPasswordDTO
{
    public function __construct(
        public string $email,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
        );
    }
}
