<?php

namespace App\DTOs\AuhDTOs;

class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email:    $data['email'],
            password: $data['password'],
        );
    }
}
