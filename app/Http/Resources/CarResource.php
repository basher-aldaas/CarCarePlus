<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'brand_id' => $this->brand_id,

            'company_id' => $this->company_id,

            'car_type_id' => $this->car_type_id,

            'plate_number' => $this->plate_number,

            'brand' => $this->brand,

            'model' => $this->model,

            'year' => $this->year,

            'color' => $this->color,

            'fuel_type'=>$this->fuel_type?->value,

            'cylinders' => $this->cylinders,

            'mileage' => $this->mileage,

            'image_url'=>$this->image_url,

            'created_at' => $this->created_at,

        ];
    }
}
