<?php

namespace App\DTOs;

class EmployeeReportDTO
{
    public function __construct(
        public ?int $order_id,
        public ?int $employee_id,
        public ?string $problem_description,
        public ?array $affected_parts,
        public ?array $images,
        public ?string $recommendation,
        public ?string $status,
        public ?string $reviewed_at,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            order_id: isset($data['order_id']) ? (int) $data['order_id'] : null,
            employee_id: isset($data['employee_id']) ? (int) $data['employee_id'] : null,
            problem_description: $data['problem_description'] ?? null,
            affected_parts: $data['affected_parts'] ?? null,
            images: $data['images'] ?? null,
            recommendation: $data['recommendation'] ?? null,
            status: $data['status'] ?? null,
            reviewed_at: $data['reviewed_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'order_id' => $this->order_id,
            'employee_id' => $this->employee_id,
            'problem_description' => $this->problem_description,
            'affected_parts' => $this->affected_parts,
            'images' => $this->images,
            'recommendation' => $this->recommendation,
            'status' => $this->status,
            'reviewed_at' => $this->reviewed_at,
        ], fn ($value) => $value !== null);
    }
}
