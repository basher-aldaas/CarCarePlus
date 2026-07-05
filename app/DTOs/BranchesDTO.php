<?php

namespace App\DTOs;

class BranchesDTO
{
    public function __construct(
        public ?int $admin_id,
        public ?string $name,
        public ?string $name_ar,
        public ?string $city,
        public ?string $address,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $phone,
        public ?bool $is_active,
        public ?array $working_hours,
        public ?bool $is_24h,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            admin_id:       isset($data['admin_id']) ? (int) $data['admin_id'] : null,
            name:           $data['name'] ?? null,
            name_ar:        $data['name_ar'] ?? null,
            city:           $data['city'] ?? null,
            address:        $data['address'] ?? null,
            latitude:       isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude:      isset($data['longitude']) ? (float) $data['longitude'] : null,
            phone:          $data['phone'] ?? null,
            is_active:      isset($data['is_active']) ? (bool) $data['is_active'] : null,
            working_hours:  isset($data['working_hours']) ? (is_array($data['working_hours']) ? $data['working_hours'] : json_decode($data['working_hours'], true)) : null,
            is_24h:         isset($data['is_24h']) ? (bool) $data['is_24h'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'admin_id' => $this->admin_id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'city' => $this->city,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'working_hours' => $this->working_hours,
            'is_24h' => $this->is_24h,
        ], fn ($value) => $value !== null);
    }
}
