<?php

namespace App\DTOs;

class SparePartRequestDTO
{
    public function __construct(
        public ?int $order_id,
        public ?int $employee_id,
        public ?int $material_id,
        public ?int $quantity,
        public ?string $specifications,
        public ?string $status,
        public ?string $notes,
        public ?string $decided_at,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            order_id: isset($data['order_id']) ? (int) $data['order_id'] : null,
            employee_id: isset($data['employee_id']) ? (int) $data['employee_id'] : null,
            material_id: isset($data['material_id']) ? (int) $data['material_id'] : null,
            quantity: isset($data['quantity']) ? (int) $data['quantity'] : null,
            specifications: $data['specifications'] ?? null,
            status: $data['status'] ?? null,
            notes: $data['notes'] ?? null,
            decided_at: $data['decided_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'order_id' => $this->order_id,
            'employee_id' => $this->employee_id,
            'material_id' => $this->material_id,
            'quantity' => $this->quantity,
            'specifications' => $this->specifications,
            'status' => $this->status,
            'notes' => $this->notes,
            'decided_at' => $this->decided_at,
        ], fn ($value) => $value !== null);
    }
}
