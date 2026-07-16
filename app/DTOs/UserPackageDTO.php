<?php

namespace App\DTOs;

class UserPackageDTO
{
    public function __construct(
        public ?int $user_id,
        public ?int $package_id,
        public ?string $start_date,
        public ?string $end_date,
        public ?int $remaining_count,
        public ?string $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user_id: isset($data['user_id']) ? (int) $data['user_id'] : null,
            package_id: isset($data['package_id']) ? (int) $data['package_id'] : null,
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            remaining_count: isset($data['remaining_count']) ? (int) $data['remaining_count'] : null,
            status: $data['status'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'user_id' => $this->user_id,
            'package_id' => $this->package_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'remaining_count' => $this->remaining_count,
            'status' => $this->status,
        ], fn ($value) => $value !== null);
    }
}