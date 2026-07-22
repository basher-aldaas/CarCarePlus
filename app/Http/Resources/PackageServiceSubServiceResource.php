<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageServiceSubServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'package_service_id' => $this->package_service_id,
            'package_service' => new PackageServiceResource($this->whenLoaded('packageService')),
            'sub_service_id' => $this->sub_service_id,
            'sub_service' => new SubServiceResource($this->whenLoaded('subService')),
            'price_override' => $this->price_override,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}