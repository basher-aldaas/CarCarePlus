<?php

namespace App\DTOs;

class RatingDTO
{
    public function __construct(
        public ?int $order_id,
        public ?int $customer_id,
        public ?int $employee_id,
        public ?int $service_rating,
        public ?int $employee_rating,
        public ?int $workshop_rating,
        public ?string $comment,
        public ?array $image_urls,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            order_id: isset($data['order_id']) ? (int) $data['order_id'] : null,
            customer_id: isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            employee_id: isset($data['employee_id']) ? (int) $data['employee_id'] : null,
            service_rating: isset($data['service_rating']) ? (int) $data['service_rating'] : null,
            employee_rating: isset($data['employee_rating']) ? (int) $data['employee_rating'] : null,
            workshop_rating: isset($data['workshop_rating']) ? (int) $data['workshop_rating'] : null,
            comment: $data['comment'] ?? null,
            image_urls: $data['image_urls'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'order_id' => $this->order_id,
            'customer_id' => $this->customer_id,
            'employee_id' => $this->employee_id,
            'service_rating' => $this->service_rating,
            'employee_rating' => $this->employee_rating,
            'workshop_rating' => $this->workshop_rating,
            'comment' => $this->comment,
            'image_urls' => $this->image_urls,
        ], fn ($value) => $value !== null);
    }
}
