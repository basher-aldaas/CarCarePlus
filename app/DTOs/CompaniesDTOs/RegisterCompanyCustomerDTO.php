<?php
namespace App\DTOs\CompaniesDTOs;

use App\DTOs\UserDTO;

class RegisterCompanyCustomerDTO
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
                'name'           => $data['company_name'],
                'name_ar'        => $data['company_name_ar'],
                'commercial_reg' => $data['commercial_reg'],
                'tax_number'     => $data['tax_number'],
                'address'        => $data['company_address'],
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
