<?php

namespace App\DTOs;

class TowingDetailDTO
{
    public function __construct(
        public ?int $order_id,
        public ?string $car_type_size,
        public ?float $destination_lat,
        public ?float $destination_lng,
        public ?string $destination_address,
        public ?string $notes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            order_id: isset($data['order_id']) ? (int) $data['order_id'] : null,
            car_type_size: $data['car_type_size'] ?? null,
            destination_lat: isset($data['destination_lat']) ? (float) $data['destination_lat'] : null,
            destination_lng: isset($data['destination_lng']) ? (float) $data['destination_lng'] : null,
            destination_address: $data['destination_address'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'order_id' => $this->order_id,
            'car_type_size' => $this->car_type_size,
            'destination_lat' => $this->destination_lat,
            'destination_lng' => $this->destination_lng,
            'destination_address' => $this->destination_address,
            'notes' => $this->notes,
        ], fn ($value) => $value !== null);
    }
}