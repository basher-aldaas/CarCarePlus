<?php


namespace App\DTOs\CarsDTOs;
use Illuminate\Http\UploadedFile;


class CreateCarDTO
{
    public function __construct(
        public readonly int     $customer_id,
        public readonly int     $brand_id,
        public readonly ?int     $company_id,
        public readonly int     $car_type_id,
        public readonly string  $plate_number,
        public readonly string  $brand,
        public readonly string  $model,
        public readonly int     $year,
        public readonly string  $color,
        public readonly string  $fuel_type,
        public readonly ?int    $cylinders,
        public readonly ?int    $mileage,
        public readonly UploadedFile|null $image
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            customer_id: auth()->id(),

            brand_id: (int)$data['brand_id'],

            company_id: isset($data['company_id'])
                ? (int) $data['company_id']
                : null,


            car_type_id: (int)$data['car_type_id'],

            plate_number: $data['plate_number'],

            brand: $data['brand'],

            model: $data['model'],

            year: (int)$data['year'],

            color: $data['color'],

            fuel_type: $data['fuel_type'],

            cylinders: isset($data['cylinders'])
                ? (int)$data['cylinders']
                : null,

            mileage: isset($data['mileage'])
                ? (int)$data['mileage']
                : null,

            image: $data['image'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([

            'customer_id' => $this->customer_id,

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

           // 'image_url' => $this->image, لأن عندك بالموديل
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
