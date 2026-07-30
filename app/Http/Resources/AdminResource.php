<?php


namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'image_url' => $this->image_url,
            'is_active' => (bool)$this->is_active,
            'role' => $this->getRoleNames()->first(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
