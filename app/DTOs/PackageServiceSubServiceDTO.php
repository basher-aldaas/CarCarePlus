<?php

namespace App\DTOs;

class PackageServiceSubServiceDTO
{
    public function __construct(
        public ?int $package_service_id,
        public ?int $sub_service_id,
        public ?float $price_override,
        public ?bool $is_active,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            package_service_id: isset($data['package_service_id']) ? (int) $data['package_service_id'] : null,
            sub_service_id: isset($data['sub_service_id']) ? (int) $data['sub_service_id'] : null,
            price_override: isset($data['price_override']) ? (float) $data['price_override'] : null,
            is_active: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'package_service_id' => $this->package_service_id,
            'sub_service_id' => $this->sub_service_id,
            'price_override' => $this->price_override,
            'is_active' => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}