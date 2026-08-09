<?php

namespace App\DTOs;

class OrderDTO
{
    public function __construct(
        public ?string $booking_group_id,
        public ?int $customer_id,
        public ?int $company_id,
        public ?int $car_id,
        public ?int $branch_id,
        public ?int $employee_id,
        public ?int $service_id,
        public ?int $category_id,
        public ?bool $booking_type,
        public ?bool $is_vip,
        public ?string $scheduled_at,
        public ?string $started_at,
        public ?string $completed_at,
        public ?string $cancelled_at,
        public ?string $cancel_reason,
        public ?float $location_lat,
        public ?float $location_lng,
        public ?string $location_address,
        public ?float $distance_km,
        public ?float $discount_amount,
        public ?float $total_price,
        public ?float $service_price,
        public ?float $sub_service_price,
        public ?float $materials_price,
        public ?string $notes,
        public ?string $status,
        public ?string $assigned_at,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            booking_group_id: $data['booking_group_id'] ?? null,
            customer_id: isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            company_id: isset($data['company_id']) ? (int) $data['company_id'] : null,
            car_id: isset($data['car_id']) ? (int) $data['car_id'] : null,
            branch_id: isset($data['branch_id']) ? (int) $data['branch_id'] : null,
            employee_id: isset($data['employee_id']) ? (int) $data['employee_id'] : null,
            service_id: isset($data['service_id']) ? (int) $data['service_id'] : null,
            category_id: isset($data['category_id']) ? (int) $data['category_id'] : null,
            booking_type: isset($data['booking_type']) ? (bool) $data['booking_type'] : null,
            is_vip: isset($data['is_vip']) ? (bool) $data['is_vip'] : null,
            scheduled_at: $data['scheduled_at'] ?? null,
            started_at: $data['started_at'] ?? null,
            completed_at: $data['completed_at'] ?? null,
            cancelled_at: $data['cancelled_at'] ?? null,
            cancel_reason: $data['cancel_reason'] ?? null,
            location_lat: isset($data['location_lat']) ? (float) $data['location_lat'] : null,
            location_lng: isset($data['location_lng']) ? (float) $data['location_lng'] : null,
            location_address: $data['location_address'] ?? null,
            distance_km: isset($data['distance_km']) ? (float) $data['distance_km'] : null,
            discount_amount: isset($data['discount_amount']) ? (float) $data['discount_amount'] : null,
            total_price: isset($data['total_price']) ? (float) $data['total_price'] : null,
            service_price: isset($data['service_price']) ? (float) $data['service_price'] : null,
            sub_service_price: isset($data['sub_service_price']) ? (float) $data['sub_service_price'] : null,
            materials_price: isset($data['materials_price']) ? (float) $data['materials_price'] : null,
            notes: $data['notes'] ?? null,
            status: $data['status'] ?? null,
            assigned_at: $data['assigned_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'booking_group_id' => $this->booking_group_id,
            'customer_id' => $this->customer_id,
            'company_id' => $this->company_id,
            'car_id' => $this->car_id,
            'branch_id' => $this->branch_id,
            'employee_id' => $this->employee_id,
            'service_id' => $this->service_id,
            'category_id' => $this->category_id,
            'booking_type' => $this->booking_type,
            'is_vip' => $this->is_vip,
            'scheduled_at' => $this->scheduled_at,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'cancelled_at' => $this->cancelled_at,
            'cancel_reason' => $this->cancel_reason,
            'location_lat' => $this->location_lat,
            'location_lng' => $this->location_lng,
            'location_address' => $this->location_address,
            'distance_km' => $this->distance_km,
            'discount_amount' => $this->discount_amount,
            'total_price' => $this->total_price,
            'service_price' => $this->service_price,
            'sub_service_price' => $this->sub_service_price,
            'materials_price' => $this->materials_price,
            'notes' => $this->notes,
            'status' => $this->status,
            'assigned_at' => $this->assigned_at,
        ], fn ($value) => $value !== null);
    }
}
