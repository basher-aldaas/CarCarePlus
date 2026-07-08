<?php

namespace App\DTOs;

class PricingRuleDTO
{
    public function __construct(
        public ?int $super_admin_id,
        public ?int $pricing_rule_type_id,
        public ?string $name,
        public ?float $value,
        public ?array $conditions,
        public ?bool $is_active,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            super_admin_id: isset($data['super_admin_id']) ? (int) $data['super_admin_id'] : null,
            pricing_rule_type_id: isset($data['pricing_rule_type_id']) ? (int) $data['pricing_rule_type_id'] : null,
            name: $data['name'] ?? null,
            value: isset($data['value']) ? (float) $data['value'] : null,
            conditions: $data['conditions'] ?? null,
            is_active: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'super_admin_id' => $this->super_admin_id,
            'pricing_rule_type_id' => $this->pricing_rule_type_id,
            'name' => $this->name,
            'value' => $this->value,
            'conditions' => $this->conditions,
            'is_active' => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}
