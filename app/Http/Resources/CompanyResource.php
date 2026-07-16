<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'name_ar'        => $this->name_ar,
            'commercial_reg' => $this->commercial_reg,
            'tax_number'     => $this->tax_number,
            'address'        => $this->address,
            'status'         => $this->status?->value,
            'is_active'      => (bool) $this->is_active,
            'owner'          => new UserResource($this->whenLoaded('owner')),
            'created_at'     => $this->created_at->toDateString(),
        ];
    }
}
