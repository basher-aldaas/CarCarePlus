<?php

namespace App\DTOs;

class CompanyDTO
{
    public function __construct(
        public ?int $customer_id,
        public ?string $name,
        public ?string $name_ar,
        public ?string $commercial_reg,
        public ?string $tax_number,
        public ?string $address,
        public ?string $status,
        public ?bool $is_active,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            customer_id:     $data['customer_id'] ?? null,
            name:            $data['name'] ?? null,
            name_ar:         $data['name_ar'] ?? null,
            commercial_reg:  $data['commercial_reg'] ?? null,
            tax_number:      $data['tax_number'] ?? null,
            address:         $data['address'] ?? null,
            status:          $data['status'] ?? null,
            is_active:       isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'customer_id' => $this->customer_id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'commercial_reg' => $this->commercial_reg,
            'tax_number' => $this->tax_number,
            'address' => $this->address,
            'status' => $this->status,
            'is_active' => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}
