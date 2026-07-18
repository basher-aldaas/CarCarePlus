<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_id' => $this->service_id,
            'service' => new ServiceResource($this->whenLoaded('service')),
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'description' => $this->description,
            'price' => $this->price,
            'is_active' => $this->is_active,
        ];
    }
}
