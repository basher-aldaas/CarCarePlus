<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkshopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'name_ar'    => $this->name_ar,
            'address'    => $this->address,
            'city'       => $this->city,
            'latitude'   => $this->latitude,
            'longitude'  => $this->longitude,
            'status'     => $this->status?->value,
            'rating_avg' => $this->rating_avg,
            'owner'      => new UserResource($this->whenLoaded('owner')),
            'created_at' => $this->created_at->toDateString(),
        ];
    }
}
