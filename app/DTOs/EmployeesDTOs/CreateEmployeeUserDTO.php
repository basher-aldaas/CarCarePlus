<?php

namespace App\DTOs\EmployeesDTOs;

use App\DTOs\UserDTO;

class CreateEmployeeUserDTO
{
    public function __construct(
        public readonly UserDTO $userDto,
        public readonly EmployeeDTO $employeeDto
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userDto: UserDTO::fromArray($data),

            employeeDto: EmployeeDTO::fromArray([
                'user_id' => $data['user_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'type' => $data['type'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'rating_avg' => $data['rating_avg'] ?? null,
            ]),
        );
    }

    public function toArray(): array
    {
        return [
            'user' => $this->userDto->toArray(),
            'employee' => $this->employeeDto->toArray(),
        ];
    }
}
