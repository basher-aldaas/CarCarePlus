<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
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
            'branch_id' => $this->branch_id,
            'branch' => $this->whenLoaded('branch', fn () => [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
                'name_ar' => $this->branch->name_ar,
            ]),
            'material_id' => $this->material_id,
            'material' => new MaterialResource($this->whenLoaded('material')),
            'quantity' => $this->quantity,
            'min_quantity' => $this->min_quantity,
            'updated_at' => $this->updated_at,
        ];
    }
}
