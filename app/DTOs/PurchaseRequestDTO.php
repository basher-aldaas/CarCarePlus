<?php

namespace App\DTOs;

class PurchaseRequestDTO
{
    public function __construct(
        public ?int $branch_id,
        public ?int $from_branch_id,
        public ?string $status,
        public ?float $total_amount,
        public ?string $notes,
        public ?bool $request_type,
        public ?string $rejection_reason,
        public ?string $approved_at,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            branch_id: isset($data['branch_id']) ? (int) $data['branch_id'] : null,
            from_branch_id: isset($data['from_branch_id']) ? (int) $data['from_branch_id'] : null,
            status: $data['status'] ?? null,
            total_amount: isset($data['total_amount']) ? (float) $data['total_amount'] : null,
            notes: $data['notes'] ?? null,
            request_type: isset($data['request_type']) ? (bool) $data['request_type'] : null,
            rejection_reason: $data['rejection_reason'] ?? null,
            approved_at: $data['approved_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'branch_id' => $this->branch_id,
            'from_branch_id' => $this->from_branch_id,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'notes' => $this->notes,
            'request_type' => $this->request_type,
            'rejection_reason' => $this->rejection_reason,
            'approved_at' => $this->approved_at,
        ], fn ($value) => $value !== null);
    }
}