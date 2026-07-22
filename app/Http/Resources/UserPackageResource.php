<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'package_id' => $this->package_id,
            'package' => new PackageResource($this->whenLoaded('package')),
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'remaining_count' => $this->remaining_count,
            'status' => $this->status?->value,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}