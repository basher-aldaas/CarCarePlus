<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'customer_id' => $this->customer_id,
            'customer' => new UserResource($this->whenLoaded('customer')),
            'employee_id' => $this->employee_id,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'service_rating' => $this->service_rating,
            'employee_rating' => $this->employee_rating,
            'workshop_rating' => $this->workshop_rating,
            'comment' => $this->comment,
            'image_urls' => $this->image_urls ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}