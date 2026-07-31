<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
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
            'admin_id' => $this->admin_id,
            'manager' => new UserResource($this->whenLoaded('manager')),
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'city' => $this->city,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'working_hours' => $this->working_hours,
            'is_24h' => $this->is_24h,
        ];
    }
}
