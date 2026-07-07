<?php

namespace App\DTOs\AuhDTOs;

use App\DTOs\UserDTO;

class RegisterAdminDTO
{
    public function __construct(
        public readonly UserDTO $user,
        public readonly int $branchId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user: UserDTO::fromArray($data),
            branchId: isset($data['branch_id']) ? (int) $data['branch_id'] : throw new \InvalidArgumentException('branch_id is required'),
        );
    }

    public function toArray(): array
    {
        return [
            'user' => $this->user->toArray(),
            'branch_id' => $this->branchId,
        ];
    }
}
