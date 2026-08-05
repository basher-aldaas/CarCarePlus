<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'payment_number' => $this->payment_number,
            'type' => $this->type?->value,
            'method' => $this->method?->value,
            'status' => $this->status?->value,
            'amount' => $this->amount,
            'points_used' => $this->points_used,
        ];
    }
}
