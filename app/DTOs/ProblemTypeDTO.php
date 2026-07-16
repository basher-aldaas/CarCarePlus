<?php

namespace App\DTOs;

class ProblemTypeDTO
{
    public function __construct(
        public ?string $name,
        public ?string $name_ar,
        public ?bool $is_active,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            name_ar: $data['name_ar'] ?? null,
            is_active: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'is_active' => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}