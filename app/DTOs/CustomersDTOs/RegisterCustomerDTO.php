<?php

namespace App\DTOs\CustomersDTOs;


use App\DTOs\UserDTO;

class RegisterCustomerDTO
{
    public function __construct(
        public readonly UserDTO $user,
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
