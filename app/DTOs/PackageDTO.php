<?php

namespace App\DTOs;

class PackageDTO
{
    public function __construct(
        public ?string $name,
        public ?string $description,
        public ?string $type,
        public ?float $price,
        public ?float $discount_pct,
        public ?int $services_count,
        public ?int $valid_days,
        public ?bool $is_active,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            type: $data['type'] ?? null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            discount_pct: isset($data['discount_pct']) ? (float) $data['discount_pct'] : null,
            services_count: isset($data['services_count']) ? (int) $data['services_count'] : null,
            valid_days: isset($data['valid_days']) ? (int) $data['valid_days'] : null,
            is_active: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'price' => $this->price,
            'discount_pct' => $this->discount_pct,
            'services_count' => $this->services_count,
            'valid_days' => $this->valid_days,
            'is_active' => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}