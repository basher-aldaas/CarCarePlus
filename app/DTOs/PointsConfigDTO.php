<?php

namespace App\DTOs;

class PointsConfigDTO
{
    public function __construct(
        public ?float $earn_per_amount,
        public ?float $redeem_value,
        public ?int $min_redeem,
        public ?int $expiry_days,
        public ?int $max_earn_per_order,
        public ?bool $is_active,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            earn_per_amount: isset($data['earn_per_amount']) ? (float) $data['earn_per_amount'] : null,
            redeem_value: isset($data['redeem_value']) ? (float) $data['redeem_value'] : null,
            min_redeem: isset($data['min_redeem']) ? (int) $data['min_redeem'] : null,
            expiry_days: isset($data['expiry_days']) ? (int) $data['expiry_days'] : null,
            max_earn_per_order: isset($data['max_earn_per_order']) ? (int) $data['max_earn_per_order'] : null,
            is_active: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'earn_per_amount' => $this->earn_per_amount,
            'redeem_value' => $this->redeem_value,
            'min_redeem' => $this->min_redeem,
            'expiry_days' => $this->expiry_days,
            'max_earn_per_order' => $this->max_earn_per_order,
            'is_active' => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}