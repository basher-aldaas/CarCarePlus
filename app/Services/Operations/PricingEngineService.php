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
        $subtotal = 0.0;

        $baseAmount = round((float) $service->base_price * $priceMultiplier, 2);
        $items[] = [
            'pricing_rule_id' => null,
            'label' => 'Base Price',
            'amount' => $baseAmount,
        ];
        $subtotal += $baseAmount;

        if ($isVip && (float) ($service->vip_extra_price ?? 0) > 0) {
            $vipAmount = (float) $service->vip_extra_price;
            $items[] = [
                'pricing_rule_id' => null,
                'label' => 'VIP Service Charge',
                'amount' => $vipAmount,
            ];
            $subtotal += $vipAmount;
        }

        if ($distanceItem = $this->buildDistanceItem($distanceKm)) {
            $items[] = $distanceItem;
            $subtotal += $distanceItem['amount'];
        }

        if ($bookingTypeItem = $this->buildBookingTypeItem($isImmediate)) {
            $items[] = $bookingTypeItem;
            $subtotal += $bookingTypeItem['amount'];
        }

        // A discount rule's amount is only ever subtracted once, via
        // discountAmount below — it isn't folded into $subtotal too, or
        // the discount would be applied twice.
        $discountAmount = 0.0;

        if ($customerTypeItem = $this->buildCustomerTypeItem($isCompanyCustomer)) {
            $items[] = $customerTypeItem;

            if ($customerTypeItem['amount'] < 0) {
                $discountAmount = abs($customerTypeItem['amount']);
            } else {
                $subtotal += $customerTypeItem['amount'];
            }
        }

        if ($dayOfWeekItem = $this->buildDayOfWeekItem($scheduledAt)) {
            $items[] = $dayOfWeekItem;
            $subtotal += $dayOfWeekItem['amount'];
        }

        $servicePrice = round($subtotal - $discountAmount, 2);

        return new PriceBreakdownDTO(
            items: $items,
            discountAmount: $discountAmount,
            servicePrice: $servicePrice,
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

        $rule = $this->pricingRuleRepository->findActiveByType('Extra Distance Charge');

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
     * Immediate (non-scheduled) bookings carry a surcharge, configured as
     * the active "booking_type" pricing rule. Scheduled bookings never
     * charge it.
     */
    protected function buildBookingTypeItem(bool $isImmediate): ?array
    {
        if (! $isImmediate) {
            return null;
        }

        $rule = $this->pricingRuleRepository->findActiveByType('Immediate Booking Charge');


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
     * Weekend surcharge — applies when the booking day is in the active
     * "day_of_week" rule's conditions.days list.
     */
    protected function buildDayOfWeekItem(Carbon $scheduledAt): ?array
    {
        $rule = $this->pricingRuleRepository->findActiveByType('Weekend Extra Charge');

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
