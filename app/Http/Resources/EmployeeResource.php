<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'branch_id'  => $this->branch_id,
            'type'       => $this->type ? 'mechanic' : 'washer',
            'is_active'  => (bool) $this->is_active,
            'rating_avg' => $this->rating_avg,
            'user'       => new UserResource($this->whenLoaded('user')),
        ];
    }
}
