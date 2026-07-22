<?php

namespace App\DTOs;

use App\Enums\PointsTransactionType;

class AdjustPointsDTO
{
    public function __construct(
        public int $customer_id,
        public PointsTransactionType $type,
        public int $points,
        public ?string $note,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            customer_id: (int) $data['customer_id'],
            type: PointsTransactionType::from($data['type']),
            points: (int) $data['points'],
            note: $data['note'] ?? null,
        );
    }
}