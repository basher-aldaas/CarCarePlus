<?php

namespace App\DTOs;

class MaintenanceDetailDTO
{
    public function __construct(
        public ?int $order_id,
        public ?int $workshop_id,
        public ?string $notes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            order_id: isset($data['order_id']) ? (int) $data['order_id'] : null,
            workshop_id: isset($data['workshop_id']) ? (int) $data['workshop_id'] : null,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'order_id' => $this->order_id,
            'workshop_id' => $this->workshop_id,
            'notes' => $this->notes,
        ], fn ($value) => $value !== null);
    }
}