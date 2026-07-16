<?php

namespace App\DTOs;

class PaymentDTO
{
    public function __construct(
        public ?int $order_id,
        public ?int $user_id,
        public ?int $package_id,
        public ?int $cash_confirmed_by,
        public ?string $payment_number,
        public ?string $type,
        public ?string $method,
        public ?string $status,
        public ?float $amount,
        public ?int $points_used,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            order_id: isset($data['order_id']) ? (int) $data['order_id'] : null,
            user_id: isset($data['user_id']) ? (int) $data['user_id'] : null,
            package_id: isset($data['package_id']) ? (int) $data['package_id'] : null,
            cash_confirmed_by: isset($data['cash_confirmed_by']) ? (int) $data['cash_confirmed_by'] : null,
            payment_number: $data['payment_number'] ?? null,
            type: $data['type'] ?? null,
            method: $data['method'] ?? null,
            status: $data['status'] ?? null,
            amount: isset($data['amount']) ? (float) $data['amount'] : null,
            points_used: isset($data['points_used']) ? (int) $data['points_used'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'package_id' => $this->package_id,
            'cash_confirmed_by' => $this->cash_confirmed_by,
            'payment_number' => $this->payment_number,
            'type' => $this->type,
            'method' => $this->method,
            'status' => $this->status,
            'amount' => $this->amount,
            'points_used' => $this->points_used,
        ], fn ($value) => $value !== null);
    }
}