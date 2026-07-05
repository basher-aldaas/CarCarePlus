<?php

namespace App\DTOs\EmployeesDTOs;


use App\DTOs\UserDTO;

class RegisterEmployeeDTO
{
    public function __construct(
        public readonly UserDTO     $user,
        public readonly EmployeeDTO $employee,
        public readonly int         $branchId,

    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user: UserDTO::fromArray($data),
            employee: EmployeeDTO::fromArray($data),
            branchId: $data['branch_id'],
        );
    }

    public function toArray(): array
    {
        return [
            'user' => $this->user->toArray(),
            'employee' => $this->employee->toArray(),
            'branch_id' => $this->branchId,
        ];
    }
}
