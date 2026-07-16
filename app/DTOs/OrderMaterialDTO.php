<?php

namespace App\DTOs;

class OrderMaterialDTO
{
    public function __construct(
        public ?int $order_id,
        public ?int $material_id,
        public ?int $requested_by,
        public ?float $quantity,
        public ?float $unit_price,
        public ?float $total_price,
        public ?string $status,
        public ?string $approved_at,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            order_id: isset($data['order_id']) ? (int) $data['order_id'] : null,
            material_id: isset($data['material_id']) ? (int) $data['material_id'] : null,
            requested_by: isset($data['requested_by']) ? (int) $data['requested_by'] : null,
            quantity: isset($data['quantity']) ? (float) $data['quantity'] : null,
            unit_price: isset($data['unit_price']) ? (float) $data['unit_price'] : null,
            total_price: isset($data['total_price']) ? (float) $data['total_price'] : null,
            status: $data['status'] ?? null,
            approved_at: $data['approved_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'order_id' => $this->order_id,
            'material_id' => $this->material_id,
            'requested_by' => $this->requested_by,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'approved_at' => $this->approved_at,
        ], fn ($value) => $value !== null);
    }
}
