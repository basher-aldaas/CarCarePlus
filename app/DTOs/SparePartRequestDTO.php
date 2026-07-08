<?php

namespace App\DTOs;

class SparePartRequestDTO
{
    public function __construct(
        public ?int $order_id,
        public ?int $employee_id,
        public ?int $admin_id,
        public ?string $part_name,
        public ?string $part_number,
        public ?string $specifications,
        public ?string $status,
        public ?string $notes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            order_id: isset($data['order_id']) ? (int) $data['order_id'] : null,
            employee_id: isset($data['employee_id']) ? (int) $data['employee_id'] : null,
            admin_id: isset($data['admin_id']) ? (int) $data['admin_id'] : null,
            part_name: $data['part_name'] ?? null,
            part_number: $data['part_number'] ?? null,
            specifications: $data['specifications'] ?? null,
            status: $data['status'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'order_id' => $this->order_id,
            'employee_id' => $this->employee_id,
            'admin_id' => $this->admin_id,
            'part_name' => $this->part_name,
            'part_number' => $this->part_number,
            'specifications' => $this->specifications,
            'status' => $this->status,
            'notes' => $this->notes,
        ], fn ($value) => $value !== null);
    }
}
