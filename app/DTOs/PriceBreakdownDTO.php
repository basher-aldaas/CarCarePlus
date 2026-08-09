<?php

namespace App\DTOs;

class PriceBreakdownDTO
{
    /**
     * @param array<int, array{pricing_rule_id: ?int, label: string, amount: float}> $items
     */
    public function __construct(
        public array $items,
        public float $discountAmount,
        public float $servicePrice,
    ) {}
}
