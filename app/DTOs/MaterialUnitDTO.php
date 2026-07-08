<?php

namespace App\DTOs;

class MaterialUnitDTO
{
    public function __construct(
        public ?string $name,
        public ?string $name_ar,
        public ?bool $is_decimal,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            name_ar: $data['name_ar'] ?? null,
            is_decimal: isset($data['is_decimal']) ? (bool) $data['is_decimal'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'is_decimal' => $this->is_decimal,
        ], fn ($value) => $value !== null);
    }
}