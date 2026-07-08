<?php

namespace App\DTOs;

class RefundDTO
{
    public function __construct(
        public ?int $payment_id,
        public ?int $order_id,
        public ?int $user_id,
        public ?float $amount,
        public ?string $reason,
        public ?string $status,
        public ?string $destination,
        public ?string $notes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            payment_id: isset($data['payment_id']) ? (int) $data['payment_id'] : null,
            order_id: isset($data['order_id']) ? (int) $data['order_id'] : null,
            user_id: isset($data['user_id']) ? (int) $data['user_id'] : null,
            amount: isset($data['amount']) ? (float) $data['amount'] : null,
            reason: $data['reason'] ?? null,
            status: $data['status'] ?? null,
            destination: $data['destination'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'payment_id' => $this->payment_id,
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'reason' => $this->reason,
            'status' => $this->status,
            'destination' => $this->destination,
            'notes' => $this->notes,
        ], fn ($value) => $value !== null);
    }
}
