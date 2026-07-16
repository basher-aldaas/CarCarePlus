<?php

namespace App\DTOs;

class OrderSubServiceDTO
{
    public function __construct(
        public ?int $order_id,
        public ?int $sub_service_id,
        public ?float $price,
        public ?string $status,
        public ?string $notes,
        public ?string $checked_at,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            order_id: isset($data['order_id']) ? (int) $data['order_id'] : null,
            sub_service_id: isset($data['sub_service_id']) ? (int) $data['sub_service_id'] : null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            status: $data['status'] ?? null,
            notes: $data['notes'] ?? null,
            checked_at: $data['checked_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'order_id' => $this->order_id,
            'sub_service_id' => $this->sub_service_id,
            'price' => $this->price,
            'status' => $this->status,
            'notes' => $this->notes,
            'checked_at' => $this->checked_at,
        ], fn ($value) => $value !== null);
    }
}
