<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderSubServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sub_service_id' => $this->sub_service_id,
            'sub_service' => new SubServiceResource($this->whenLoaded('subService')),
            'price' => $this->price,
            'status' => $this->status?->value,
            'notes' => $this->notes,
            'checked_at' => $this->checked_at?->toIso8601String(),
        ];
    }
}