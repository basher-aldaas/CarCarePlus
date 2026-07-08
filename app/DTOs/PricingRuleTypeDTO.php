<?php

namespace App\DTOs;

class PricingRuleTypeDTO
{
    public function __construct(
        public ?string $name,
        public ?string $name_ar,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            name_ar: $data['name_ar'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'name_ar' => $this->name_ar,
        ], fn ($value) => $value !== null);
    }
}