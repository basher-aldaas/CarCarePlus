<?php

namespace App\DTOs;

class UserPointDTO
{
    public function __construct(
        public ?int $customer_id,
        public ?int $balance,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            customer_id: isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            balance: isset($data['balance']) ? (int) $data['balance'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'customer_id' => $this->customer_id,
            'balance' => $this->balance,
        ], fn ($value) => $value !== null);
    }
}