<?php

namespace App\DTOs\AuthDTOs;

class ChangePasswordDTO
{
    public function __construct(
        public string $currentPassword,
        public string $newPassword,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            currentPassword: $data['current_password'],
            newPassword:     $data['password'],
        );
    }
}
