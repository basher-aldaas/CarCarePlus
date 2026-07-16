<?php

namespace App\DTOs;

class ContractDTO
{
    public function __construct(
        public ?int $company_id,
        public ?int $created_by,
        public ?int $workshop_id,
        public ?string $title,
        public ?float $value,
        public ?string $start_date,
        public ?string $end_date,
        public ?string $terms,
        public ?string $file_url,
        public ?string $status,
        public ?int $renewal_count,
        public ?string $last_renewed_at,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            company_id: isset($data['company_id']) ? (int) $data['company_id'] : null,
            created_by: isset($data['created_by']) ? (int) $data['created_by'] : null,
            workshop_id: isset($data['workshop_id']) ? (int) $data['workshop_id'] : null,
            title: $data['title'] ?? null,
            value: isset($data['value']) ? (float) $data['value'] : null,
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            terms: $data['terms'] ?? null,
            file_url: $data['file_url'] ?? null,
            status: $data['status'] ?? null,
            renewal_count: isset($data['renewal_count']) ? (int) $data['renewal_count'] : null,
            last_renewed_at: $data['last_renewed_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'company_id' => $this->company_id,
            'created_by' => $this->created_by,
            'workshop_id' => $this->workshop_id,
            'title' => $this->title,
            'value' => $this->value,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'terms' => $this->terms,
            'file_url' => $this->file_url,
            'status' => $this->status,
            'renewal_count' => $this->renewal_count,
            'last_renewed_at' => $this->last_renewed_at,
        ], fn ($value) => $value !== null);
    }
}
