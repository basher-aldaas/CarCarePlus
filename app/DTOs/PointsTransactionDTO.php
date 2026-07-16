<?php

namespace App\DTOs;

class PointsTransactionDTO
{
    public function __construct(
        public ?int $customer_id,
        public ?string $type,
        public ?int $points,
        public ?int $balance_before,
        public ?int $balance_after,
        public ?string $reference_type,
        public ?int $reference_id,
        public ?string $expires_at,
        public ?string $note,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            customer_id: isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            type: $data['type'] ?? null,
            points: isset($data['points']) ? (int) $data['points'] : null,
            balance_before: isset($data['balance_before']) ? (int) $data['balance_before'] : null,
            balance_after: isset($data['balance_after']) ? (int) $data['balance_after'] : null,
            reference_type: $data['reference_type'] ?? null,
            reference_id: isset($data['reference_id']) ? (int) $data['reference_id'] : null,
            expires_at: $data['expires_at'] ?? null,
            note: $data['note'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'customer_id' => $this->customer_id,
            'type' => $this->type,
            'points' => $this->points,
            'balance_before' => $this->balance_before,
            'balance_after' => $this->balance_after,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'expires_at' => $this->expires_at,
            'note' => $this->note,
        ], fn ($value) => $value !== null);
    }
}