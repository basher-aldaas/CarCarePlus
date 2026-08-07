<?php

namespace App\Services\Operations;

use App\DTOs\PriceBreakdownDTO;
use App\Models\Service;
use App\Repositories\Eloquent\PricingRuleRepository;
use App\Support\TimeWindow;
use Carbon\Carbon;

/**
 * Turns the admin-configured pricing_rules into the price breakdown that
 * gets snapshotted onto an order at booking time, one line item per applied
 * charge. Rule values are always a flat price (never a percentage).
 */
class PricingEngineService
{
    /**
     * Category name -> the "service_type" condition value used by the
     * seeded "service" pricing rules. Categories with no mapping (road
     * assistance, towing) carry no service-type surcharge.
     */
    protected const CATEGORY_SERVICE_TYPES = [
        'Car Wash' => 'washing',
        'Maintenance' => 'maintenance',
    ];

    public function __construct(protected PricingRuleRepository $pricingRuleRepository)
    {}

    public function calculate(
        Service $service,
        bool $isVip,
        ?float $distanceKm,
        Carbon $scheduledAt,
        bool $isImmediate = false,
        float $priceMultiplier = 1.0,
        bool $isCompanyCustomer = false,
    ): PriceBreakdownDTO {
        $items = [];

        $items[] = [
            'pricing_rule_id' => null,
            'label' => 'Base Price',
            'amount' => round((float) $service->base_price * $priceMultiplier, 2),
        ];

        if ($isVip && (float) ($service->vip_extra_price ?? 0) > 0) {
            $items[] = [
                'pricing_rule_id' => null,
                'label' => 'VIP Service Charge',
                'amount' => (float) $service->vip_extra_price,
            ];
        }

        if ($distanceItem = $this->buildDistanceItem($distanceKm)) {
            $items[] = $distanceItem;
        }

        if ($offHoursItem = $this->buildOffHoursItem($scheduledAt)) {
            $items[] = $offHoursItem;
        }

        if ($bookingTypeItem = $this->buildBookingTypeItem($isImmediate)) {
            $items[] = $bookingTypeItem;
        }

        if ($customerTypeItem = $this->buildCustomerTypeItem($isCompanyCustomer)) {
            $items[] = $customerTypeItem;
        }

        if ($serviceTypeItem = $this->buildServiceTypeItem($service)) {
            $items[] = $serviceTypeItem;
        }

        if ($dayOfWeekItem = $this->buildDayOfWeekItem($scheduledAt)) {
            $items[] = $dayOfWeekItem;
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
     * Surcharges whenever the booking time falls inside the active
     * "off_hours" rule's own conditions.start_time/end_time window — one
     * global window (e.g. 18:00-08:00), independent of any branch.
     */
    protected function buildOffHoursItem(Carbon $scheduledAt): ?array
    {
        $rule = $this->pricingRuleRepository->findActiveByType('off_hours');

        if (! $rule) {
            return null;
        }

        $start = $rule->conditions['start_time'] ?? null;
        $end = $rule->conditions['end_time'] ?? null;

        if (! $start || ! $end) {
            return null;
        }

        if (! TimeWindow::contains($scheduledAt->format('H:i'), $start, $end)) {
            return null;
        }

        return [
            'pricing_rule_id' => $rule->id,
            'label' => $rule->name,
            'amount' => (float) $rule->value,
        ];
    }

    /**
     * Immediate (non-scheduled) bookings carry a surcharge, configured as
     * the active "booking_type" pricing rule. Scheduled bookings never
     * charge it.
     */
    protected function buildBookingTypeItem(bool $isImmediate): ?array
    {
        if (! $isImmediate) {
            return null;
        }

        $rule = $this->pricingRuleRepository->findActiveByType('booking_type');

        if (! $rule) {
            return null;
        }

        return [
            'pricing_rule_id' => $rule->id,
            'label' => $rule->name,
            'amount' => (float) $rule->value,
        ];
    }

    /**
     * A company customer and a personal customer each have their own
     * active "customer_type" rule (typically a discount for company,
     * zero for personal).
     */
    protected function buildCustomerTypeItem(bool $isCompanyCustomer): ?array
    {
        $rule = $this->pricingRuleRepository->findActiveByTypeAndCondition(
            'customer_type',
            'customer_type',
            $isCompanyCustomer ? 'company' : 'personal',
        );

        if (! $rule) {
            return null;
        }

        return [
            'pricing_rule_id' => $rule->id,
            'label' => $rule->name,
            'amount' => (float) $rule->value,
        ];
    }

    /**
     * Washing/maintenance categories carry their own "service" surcharge;
     * categories with no mapping (road assistance, towing) never do.
     */
    protected function buildServiceTypeItem(Service $service): ?array
    {
        $serviceType = self::CATEGORY_SERVICE_TYPES[$service->category?->name] ?? null;

        if (! $serviceType) {
            return null;
        }

        $rule = $this->pricingRuleRepository->findActiveByTypeAndCondition('service', 'service_type', $serviceType);

        if (! $rule) {
            return null;
        }

        return [
            'pricing_rule_id' => $rule->id,
            'label' => $rule->name,
            'amount' => (float) $rule->value,
        ];
    }

    /**
     * Weekend surcharge — applies when the booking day is in the active
     * "day_of_week" rule's conditions.days list.
     */
    protected function buildDayOfWeekItem(Carbon $scheduledAt): ?array
    {
        $rule = $this->pricingRuleRepository->findActiveByType('day_of_week');

        if (! $rule) {
            return null;
        }

        $days = array_map('strtolower', $rule->conditions['days'] ?? []);

        if (! in_array(strtolower($scheduledAt->englishDayOfWeek), $days, true)) {
            return null;
        }

        return [
            'pricing_rule_id' => $rule->id,
            'label' => $rule->name,
            'amount' => (float) $rule->value,
        ];
    }
}
