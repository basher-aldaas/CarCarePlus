<?php


namespace App\DTOs\CarsDTOs;

use Illuminate\Http\UploadedFile;

class UpdateCarDTO
{
    public function __construct(
        public readonly ?int    $brand_id,
        public readonly ?int    $company_id,
        public readonly ?int    $car_type_id,
        public readonly ?string $plate_number,
        public readonly ?string $brand,
        public readonly ?string $model,
        public readonly ?int    $year,
        public readonly ?string $color,
        public readonly ?string $fuel_type,
        public readonly ?int    $cylinders,
        public readonly ?int    $mileage,
        public readonly UploadedFile|null $image,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(

            brand_id: isset($data['brand_id'])
                ? (int)$data['brand_id']
                : null,

            company_id: isset($data['company_id'])
                ? (int)$data['company_id']
                : null,

            car_type_id: isset($data['car_type_id'])
                ? (int)$data['car_type_id']
                : null,

            plate_number: $data['plate_number'] ?? null,

            brand: $data['brand'] ?? null,

            model: $data['model'] ?? null,

            year: isset($data['year'])
                ? (int)$data['year']
                : null,

            color: $data['color'] ?? null,

            fuel_type: $data['fuel_type'] ?? null,

            cylinders: isset($data['cylinders'])
                ? (int)$data['cylinders']
                : null,

            mileage: isset($data['mileage'])
                ? (int)$data['mileage']
                : null,

            image: $data['image'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([

            'brand_id' => $this->brand_id,

            'company_id' => $this->company_id,

            'car_type_id' => $this->car_type_id,

            'plate_number' => $this->plate_number,

            'brand' => $this->brand,

            'model' => $this->model,

            'year' => $this->year,

            'color' => $this->color,

            'fuel_type' => $this->fuel_type,

            'cylinders' => $this->cylinders,

            'mileage' => $this->mileage,

          //  'image' => $this->image, because لأن عندك بالموديل
            //
            //image_url
            //
            //وليس
            //
            //image
            //
            //والـ DTO ليس مسؤولاً عن تحويل الملف إلى URL.
            //
            //الـ Service هو المسؤول.

        ], fn($value) => $value !== null);
    }
}
