<?php

namespace App\DTOs;

class MaterialDTO
{
    public function __construct(
        public ?int $material_unit_id,
        public ?string $name,
        public ?string $name_ar,
        public ?string $description,
        public ?float $unit_price,
        public ?bool $is_vip_material,
        public ?bool $is_active,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            material_unit_id: isset($data['material_unit_id']) ? (int) $data['material_unit_id'] : null,
            name: $data['name'] ?? null,
            name_ar: $data['name_ar'] ?? null,
            description: $data['description'] ?? null,
            unit_price: isset($data['unit_price']) ? (float) $data['unit_price'] : null,
            is_vip_material: isset($data['is_vip_material']) ? (bool) $data['is_vip_material'] : null,
            is_active: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'material_unit_id' => $this->material_unit_id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'description' => $this->description,
            'unit_price' => $this->unit_price,
            'is_vip_material' => $this->is_vip_material,
            'is_active' => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}