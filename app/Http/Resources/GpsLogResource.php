<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GpsLogResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'order_id' => $this->order_id,
            'order' => new OrderResource($this->whenLoaded('order')),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'recorded_at' => $this->recorded_at,
            'created_at' => $this->created_at,
        ];
    }
}