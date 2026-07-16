<?php

namespace App\DTOs;

class PurchaseRequestItemDTO
{
    public function __construct(
        public ?int $purchase_req_id,
        public ?int $material_id,
        public ?float $quantity,
        public ?float $unit_price,
        public ?float $total_price,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            purchase_req_id: isset($data['purchase_req_id']) ? (int) $data['purchase_req_id'] : null,
            material_id: isset($data['material_id']) ? (int) $data['material_id'] : null,
            quantity: isset($data['quantity']) ? (float) $data['quantity'] : null,
            unit_price: isset($data['unit_price']) ? (float) $data['unit_price'] : null,
            total_price: isset($data['total_price']) ? (float) $data['total_price'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'purchase_req_id' => $this->purchase_req_id,
            'material_id' => $this->material_id,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
        ], fn ($value) => $value !== null);
    }
}