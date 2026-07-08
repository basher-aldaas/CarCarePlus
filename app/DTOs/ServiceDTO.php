<?php

namespace App\DTOs;

class ServiceDTO
{
    public function __construct(
        public ?int $category_id,
        public ?string $name,
        public ?string $name_ar,
        public ?string $description,
        public ?float $base_price,
        public ?bool $is_vip_available,
        public ?float $vip_extra_price,
        public ?int $duration_minutes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            category_id: isset($data['category_id']) ? (int) $data['category_id'] : null,
            name: $data['name'] ?? null,
            name_ar: $data['name_ar'] ?? null,
            description: $data['description'] ?? null,
            base_price: isset($data['base_price']) ? (float) $data['base_price'] : null,
            is_vip_available: isset($data['is_vip_available']) ? (bool) $data['is_vip_available'] : null,
            vip_extra_price: isset($data['vip_extra_price']) ? (float) $data['vip_extra_price'] : null,
            duration_minutes: isset($data['duration_minutes']) ? (int) $data['duration_minutes'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'category_id' => $this->category_id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'description' => $this->description,
            'base_price' => $this->base_price,
            'is_vip_available' => $this->is_vip_available,
            'vip_extra_price' => $this->vip_extra_price,
            'duration_minutes' => $this->duration_minutes,
        ], fn ($value) => $value !== null);
    }
}
