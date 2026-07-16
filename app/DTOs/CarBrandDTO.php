<?php

namespace App\DTOs;

class CarBrandDTO
{
    public function __construct(
        public ?string $name,
        public ?string $logo,
        public ?bool $is_active,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            logo: $data['logo'] ?? null,
            is_active: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'logo' => $this->logo,
            'is_active' => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}
