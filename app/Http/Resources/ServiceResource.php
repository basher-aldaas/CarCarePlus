<?php


namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'category_id' => $this->category_id,

            'name' => $this->name,

            'name_ar' => $this->name_ar,

            'description' => $this->description,

            'base_price' => $this->base_price,

            'is_vip_available' => $this->is_vip_available,

            'vip_extra_price' => $this->vip_extra_price,

            'duration_minutes' => $this->duration_minutes,

            'created_at' => $this->created_at
                ? $this->created_at->toDateTimeString()
                : null,

            'updated_at' => $this->updated_at
                ? $this->updated_at->toDateTimeString()
                : null,

        ];
    }
}
