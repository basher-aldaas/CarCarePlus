<?php

namespace App\DTOs;

class EmployeeDTO
{
    public function __construct(
        public ?int $user_id,
        public ?int $branch_id,
        public ?bool $type,
        public ?bool $is_active,
        public ?float $rating_avg,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user_id:        isset($data['user_id']) ? (int) $data['user_id'] : null,
            branch_id:      $data['branch_id'] ?? null,
            type:           isset($data['type']) ? self::normalizeType($data['type']) : null,

            is_active:      isset($data['is_active']) ? (bool) $data['is_active'] : null,
            rating_avg:     isset($data['rating_avg']) ? (float) $data['rating_avg'] : null,
        );
    }

    /**
     * Map the incoming employee type to the boolean column.
     * false (0) = washer, true (1) = mechanic.
     */
    private static function normalizeType(mixed $type): bool
    {
        return in_array($type, ['mechanic', 1, '1', true], true);
    }

    public function toArray(): array
    {
        return array_filter([
            'user_id' => $this->user_id,
            'branch_id' => $this->branch_id,
            'type' => $this->type,
            'is_active' => $this->is_active,
            'rating_avg' => $this->rating_avg,
        ], fn ($value) => $value !== null);
    }
}
