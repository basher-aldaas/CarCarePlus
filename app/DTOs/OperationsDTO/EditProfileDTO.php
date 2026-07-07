<?php

namespace App\DTOs\OperationsDTO;


use App\DTOs\UserDTO;

class EditProfileDTO
{
    public function __construct(
        public readonly UserDto $user,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user: UserDTO::fromArray($data),
        );
    }

    public function toArray(): array
    {
        return [
            'user' => $this->user->toArray(),
        ];
    }
}
