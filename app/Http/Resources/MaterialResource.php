<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
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
            'material_unit_id' => $this->material_unit_id,
            'unit' => new MaterialUnitResource($this->whenLoaded('unit')),
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'description' => $this->description,
            'unit_price' => $this->unit_price,
            'is_visible_to_customer' => $this->is_visible_to_customer,
            'is_active' => $this->is_active,
        ];
    }
}
