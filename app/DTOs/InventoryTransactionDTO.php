<?php

namespace App\DTOs;

class InventoryTransactionDTO
{
    public function __construct(
        public ?int $branch_id,
        public ?int $material_id,
        public ?int $created_by,
        public ?string $type,
        public ?float $quantity,
        public ?float $quantity_before,
        public ?float $quantity_after,
        public ?string $reference_id,
        public ?string $note,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            branch_id: isset($data['branch_id']) ? (int) $data['branch_id'] : null,
            material_id: isset($data['material_id']) ? (int) $data['material_id'] : null,
            created_by: isset($data['created_by']) ? (int) $data['created_by'] : null,
            type: $data['type'] ?? null,
            quantity: isset($data['quantity']) ? (float) $data['quantity'] : null,
            quantity_before: isset($data['quantity_before']) ? (float) $data['quantity_before'] : null,
            quantity_after: isset($data['quantity_after']) ? (float) $data['quantity_after'] : null,
            reference_id: $data['reference_id'] ?? null,
            note: $data['note'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'branch_id' => $this->branch_id,
            'material_id' => $this->material_id,
            'created_by' => $this->created_by,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'quantity_before' => $this->quantity_before,
            'quantity_after' => $this->quantity_after,
            'reference_id' => $this->reference_id,
            'note' => $this->note,
        ], fn ($value) => $value !== null);
    }
}