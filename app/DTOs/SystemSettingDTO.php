<?php

namespace App\DTOs;

class SystemSettingDTO
{
    public function __construct(
        public ?string $key,
        public ?string $value,
        public ?string $type,
        public ?string $description,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'] ?? null,
            value: $data['value'] ?? null,
            type: $data['type'] ?? null,
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type,
            'description' => $this->description,
        ], fn ($value) => $value !== null);
    }
}
