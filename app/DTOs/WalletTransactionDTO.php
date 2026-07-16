<?php

namespace App\DTOs;

class WalletTransactionDTO
{
    public function __construct(
        public ?int $wallet_id,
        public ?int $user_id,
        public ?string $type,
        public ?string $reason,
        public ?float $amount,
        public ?float $balance_before,
        public ?float $balance_after,
        public ?string $note,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            wallet_id: isset($data['wallet_id']) ? (int) $data['wallet_id'] : null,
            user_id: isset($data['user_id']) ? (int) $data['user_id'] : null,
            type: $data['type'] ?? null,
            reason: $data['reason'] ?? null,
            amount: isset($data['amount']) ? (float) $data['amount'] : null,
            balance_before: isset($data['balance_before']) ? (float) $data['balance_before'] : null,
            balance_after: isset($data['balance_after']) ? (float) $data['balance_after'] : null,
            note: $data['note'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'wallet_id' => $this->wallet_id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'reason' => $this->reason,
            'amount' => $this->amount,
            'balance_before' => $this->balance_before,
            'balance_after' => $this->balance_after,
            'note' => $this->note,
        ], fn ($value) => $value !== null);
    }
}
