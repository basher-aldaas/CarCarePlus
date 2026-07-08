<?php

namespace App\DTOs;

class InventoryDTO
{
    public function __construct(
        public ?int $branch_id,
        public ?int $material_id,
        public ?float $quantity,
        public ?float $min_quantity,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            branch_id: isset($data['branch_id']) ? (int) $data['branch_id'] : null,
            material_id: isset($data['material_id']) ? (int) $data['material_id'] : null,
            quantity: isset($data['quantity']) ? (float) $data['quantity'] : null,
            min_quantity: isset($data['min_quantity']) ? (float) $data['min_quantity'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'branch_id' => $this->branch_id,
            'material_id' => $this->material_id,
            'quantity' => $this->quantity,
            'min_quantity' => $this->min_quantity,
        ], fn ($value) => $value !== null);
    }
}