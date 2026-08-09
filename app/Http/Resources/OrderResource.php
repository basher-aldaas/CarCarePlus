<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'booking_group_id' => $this->booking_group_id,

            'customer_id' => $this->customer_id,
            'customer' => new UserResource($this->whenLoaded('customer')),

            'company_id' => $this->company_id,

            'car_id' => $this->car_id,
            'car' => new CarResource($this->whenLoaded('car')),

            'branch_id' => $this->branch_id,
            'branch' => new BranchResource($this->whenLoaded('branch')),

            'employee_id' => $this->employee_id,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),

            'service_id' => $this->service_id,
            'service' => new ServiceResource($this->whenLoaded('service')),

            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),

            'booking_type' => (bool) $this->booking_type,
            'is_vip' => (bool) $this->is_vip,
            'status' => $this->status?->value,

            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'cancel_reason' => $this->cancel_reason,
            'assigned_at' => $this->assigned_at?->toIso8601String(),

            'location_lat' => $this->location_lat,
            'location_lng' => $this->location_lng,
            'location_address' => $this->location_address,
            'distance_km' => $this->distance_km,

            'discount_amount' => $this->discount_amount,
            'service_price' => $this->service_price,
            'sub_service_price' => $this->sub_service_price,
            'materials_price' => $this->materials_price,
            'total_price' => $this->total_price,
            'price_items' => OrderPriceItemResource::collection($this->whenLoaded('priceItems')),
            'sub_services' => OrderSubServiceResource::collection($this->whenLoaded('subServices')),
            'materials' => OrderMaterialResource::collection($this->whenLoaded('materials')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),

            'notes' => $this->notes,

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}