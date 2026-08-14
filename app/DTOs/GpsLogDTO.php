<?php

namespace App\DTOs;

class GpsLogDTO
{
    public function __construct(
        public ?int $employee_id,
        public ?int $order_id,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $recorded_at,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            employee_id: isset($data['employee_id']) ? (int) $data['employee_id'] : null,
            order_id: isset($data['order_id']) ? (int) $data['order_id'] : null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            recorded_at: $data['recorded_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'employee_id' => $this->employee_id,
            'order_id' => $this->order_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'recorded_at' => $this->recorded_at,
        ], fn ($value) => $value !== null);
    }
}