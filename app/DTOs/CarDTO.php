<?php

namespace App\DTOs;

class CarDTO
{
    public function __construct(
        public ?int $user_id,
        public ?int $brand_id,
        public ?int $car_type_id,
        public ?int $branch_id,
        public ?string $plate_number,
        public ?string $model,
        public ?int $year,
        public ?string $color,
        public ?string $fuel_type,
        public ?int $cylinders,
        public ?int $mileage,
        public ?string $image_url,
        public ?bool $is_active,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user_id: isset($data['user_id']) ? (int) $data['user_id'] : null,
            brand_id: isset($data['brand_id']) ? (int) $data['brand_id'] : null,
            car_type_id: isset($data['car_type_id']) ? (int) $data['car_type_id'] : null,
            branch_id: isset($data['branch_id']) ? (int) $data['branch_id'] : null,

            plate_number: $data['plate_number'] ?? null,
            model: $data['model'] ?? null,

            year: isset($data['year']) ? (int) $data['year'] : null,
            color: $data['color'] ?? null,

            fuel_type: $data['fuel_type'] ?? null,
            cylinders: isset($data['cylinders']) ? (int) $data['cylinders'] : null,
            mileage: isset($data['mileage']) ? (int) $data['mileage'] : null,

            image_url: $data['image_url'] ?? null,

            is_active: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'user_id' => $this->user_id,
            'brand_id' => $this->brand_id,
            'car_type_id' => $this->car_type_id,
            'branch_id' => $this->branch_id,
            'plate_number' => $this->plate_number,
            'model' => $this->model,
            'year' => $this->year,
            'color' => $this->color,
            'fuel_type' => $this->fuel_type,
            'cylinders' => $this->cylinders,
            'mileage' => $this->mileage,
            'image_url' => $this->image_url,
            'is_active' => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}
