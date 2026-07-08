<?php

namespace App\DTOs;

class SubServiceDTO
{
    public function __construct(
        public ?int $service_id,
        public ?string $name,
        public ?string $name_ar,
        public ?string $description,
        public ?float $price,
        public ?bool $is_active,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            service_id: isset($data['service_id']) ? (int) $data['service_id'] : null,
            name: $data['name'] ?? null,
            name_ar: $data['name_ar'] ?? null,
            description: $data['description'] ?? null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            is_active: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'service_id' => $this->service_id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'description' => $this->description,
            'price' => $this->price,
            'is_active' => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}
