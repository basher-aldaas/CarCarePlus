<?php

namespace App\DTOs;

class WalletDTO
{
    public function __construct(
        public ?int $user_id,
        public ?float $balance,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user_id: isset($data['user_id']) ? (int) $data['user_id'] : null,
            balance: isset($data['balance']) ? (float) $data['balance'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'user_id' => $this->user_id,
            'balance' => $this->balance,
        ], fn ($value) => $value !== null);
    }
}