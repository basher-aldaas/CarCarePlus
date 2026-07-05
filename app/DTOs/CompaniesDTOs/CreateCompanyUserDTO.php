<?php

namespace App\DTOs\CompaniesDTOs;

use App\DTOs\UserDTO;

class CreateCompanyUserDTO
{
    public function __construct(
        public readonly UserDTO $userDto,
        public readonly CompanyDTO $companyDto
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userDto: UserDTO::fromArray($data),

            companyDto: CompanyDTO::fromArray([
                'name' => $data['company_name'] ?? null,
                'name_ar' => $data['company_name_ar'] ?? null,
                'commercial_reg' => $data['commercial_reg'] ?? null,
                'tax_number' => $data['tax_number'] ?? null,
                'address' => $data['company_address'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]),
        );
    }

    public function toArray(): array
    {
        return [
            'user' => $this->userDto->toArray(),
            'company' => $this->companyDto->toArray(),
        ];
    }
}
