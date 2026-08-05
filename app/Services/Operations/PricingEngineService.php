<?php

namespace App\Services\Operations;

use App\DTOs\PriceBreakdownDTO;
use App\Models\Branch;
use App\Models\Service;
use App\Repositories\Eloquent\PricingRuleRepository;
use Carbon\Carbon;

/**
 * Turns the admin-configured pricing_rules into the price breakdown that
 * gets snapshotted onto an order at booking time, one line item per applied
 * charge. Rule values are always a flat price (never a percentage).
 */
class PricingEngineService
{
    public function __construct(protected PricingRuleRepository $pricingRuleRepository)
    {}

    public function calculate(
        Service $service,
        bool $isVip,
        ?float $distanceKm,
        ?Branch $branch,
        Carbon $scheduledAt,
    ): PriceBreakdownDTO {
        $items = [];

        $items[] = [
            'pricing_rule_id' => null,
            'label' => 'Base Price',
            'amount' => (float) $service->base_price,
        ];

        if ($isVip && (float) ($service->vip_extra_price ?? 0) > 0) {
            $items[] = [
                'pricing_rule_id' => null,
                'label' => 'VIP Charge',
                'amount' => (float) $service->vip_extra_price,
            ];
        }

        if ($distanceItem = $this->buildDistanceItem($distanceKm)) {
            $items[] = $distanceItem;
        }

        if ($offHoursItem = $this->buildOffHoursItem($branch, $scheduledAt)) {
            $items[] = $offHoursItem;
        }

        $discountAmount = 0.0;
        $totalPrice = array_sum(array_column($items, 'amount')) - $discountAmount;

        return new PriceBreakdownDTO(
            items: $items,
            discountAmount: $discountAmount,
            totalPrice: round($totalPrice, 2),
        );
    }

    /**
     * Every booking has a free distance allowance (the rule's
     * conditions.included_km); only the km beyond that are charged, at the
     * rule's value per km.
     */
    protected function buildDistanceItem(?float $distanceKm): ?array
    {
        if (! $distanceKm) {
            return null;
        }

        $rule = $this->pricingRuleRepository->findActiveByType('distance');

        if (! $rule) {
            return null;
        }

        $includedKm = (float) ($rule->conditions['included_km'] ?? 0);
        $extraKm = max(0.0, $distanceKm - $includedKm);

        if ($extraKm <= 0) {
            return null;
        }

        return [
            'pricing_rule_id' => $rule->id,
            'label' => $rule->name,
            'amount' => round($extraKm * $rule->value, 2),
        ];
    }

    /**
     * A 24h branch never charges an off-hours surcharge. Otherwise the
     * surcharge applies whenever the booking time falls outside the
     * branch's working_hours window (a window may cross midnight).
     */
    protected function buildOffHoursItem(?Branch $branch, Carbon $scheduledAt): ?array
    {
        if (! $branch || $branch->is_24h) {
            return null;
        }

        $hours = $branch->working_hours;

        if (empty($hours['start']) || empty($hours['end'])) {
            return null;
        }

        $time = $scheduledAt->format('H:i');
        $start = $hours['start'];
        $end = $hours['end'];

        $withinWorkingHours = $start <= $end
            ? ($time >= $start && $time <= $end)
            : ($time >= $start || $time <= $end);

        if ($withinWorkingHours) {
            return null;
        }

        $rule = $this->pricingRuleRepository->findActiveByType('off_hours');

        if (! $rule) {
            return null;
        }

        return [
            'pricing_rule_id' => $rule->id,
            'label' => $rule->name,
            'amount' => (float) $rule->value,
        ];
    }
}
