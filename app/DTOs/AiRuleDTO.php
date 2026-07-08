<?php

namespace App\DTOs;

class AiRuleDTO
{
    public function __construct(
        public ?int $brand_id,
        public ?string $name,
        public ?string $name_ar,
        public ?string $type,
        public ?string $condition_key,
        public ?string $condition_value,
        public ?string $car_type,
        public ?string $fuel_type,
        public ?string $response_template,
        public ?bool $is_active,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            brand_id: isset($data['brand_id']) ? (int) $data['brand_id'] : null,
            name: $data['name'] ?? null,
            name_ar: $data['name_ar'] ?? null,
            type: $data['type'] ?? null,
            condition_key: $data['condition_key'] ?? null,
            condition_value: $data['condition_value'] ?? null,
            car_type: $data['car_type'] ?? null,
            fuel_type: $data['fuel_type'] ?? null,
            response_template: $data['response_template'] ?? null,
            is_active: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'brand_id' => $this->brand_id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'type' => $this->type,
            'condition_key' => $this->condition_key,
            'condition_value' => $this->condition_value,
            'car_type' => $this->car_type,
            'fuel_type' => $this->fuel_type,
            'response_template' => $this->response_template,
            'is_active' => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}
