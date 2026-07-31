<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiRuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'brand_id' => $this->brand_id,
            'brand' => new CarBrandResource($this->whenLoaded('brand')),
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'type' => $this->type,
            'condition_key' => $this->condition_key,
            'condition_value' => $this->condition_value,
            'car_type' => $this->car_type,
            'fuel_type' => $this->fuel_type,
            'response_template' => $this->response_template,
            'is_active' => $this->is_active,
        ];
    }
}
