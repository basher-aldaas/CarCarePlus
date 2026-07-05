<?php

namespace App\DTOs\WorkshopsDTOs;


class WorkshopDTO
{
    public function __construct(
        public ?int    $user_id,
        public ?string $name,
        public ?string $name_ar,
        public ?string $phone,
        public ?string $address,
        public ?string $city,
        public ?float  $latitude,
        public ?float  $longitude,
        public ?string $status,
        public ?float  $rating_avg,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user_id:        isset($data['user_id']) ? (int)$data['user_id'] : null,
            name:           $data['name'] ?? null,
            name_ar:        $data['name_ar'] ?? null,
            phone:          $data['phone'] ?? null,
            address:        $data['address'] ?? null,
            city:           $data['city'] ?? null,
            latitude:       isset($data['latitude']) ? (float)$data['latitude'] : null,
            longitude:      isset($data['longitude']) ? (float)$data['longitude'] : null,
            status:         $data['status'] ?? null,
            rating_avg:     isset($data['rating_avg']) ? (float)$data['rating_avg'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'user_id' => $this->user_id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'rating_avg' => $this->rating_avg,
        ], fn ($value) => $value !== null);
    }
}
