<?php

namespace App\DTOs;

class SuggestedProblemDTO
{
    public function __construct(
        public ?string $name,
        public ?string $name_ar,
        public ?string $description,
        public ?string $category,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            name_ar: $data['name_ar'] ?? null,
            description: $data['description'] ?? null,
            category: $data['category'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'description' => $this->description,
            'category' => $this->category,
        ], fn ($value) => $value !== null);
    }
}