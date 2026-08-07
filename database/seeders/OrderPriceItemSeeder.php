<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderPriceItem;
use Illuminate\Database\Seeder;

/**
 * Breaks each order's own total_price back down into the "Base Price" /
 * "VIP Charge" line items that PricingEngineService would have produced at
 * booking time, so sum(order_price_items.amount) - order.discount_amount
 * always equals order.total_price exactly — matching the real invariant the
 * booking flow relies on.
 */
class OrderPriceItemSeeder extends Seeder
{
    public function run(): void
    {
        Order::with('service')->chunk(50, function ($orders) {
            foreach ($orders as $order) {
                $grossAmount = (float) $order->total_price + (float) $order->discount_amount;

                $vipAmount = $order->is_vip
                    ? min((float) ($order->service?->vip_extra_price ?? 50), $grossAmount)
                    : 0.0;

                $baseAmount = round($grossAmount - $vipAmount, 2);

                $this->createItem($order, null, 'Base Price', $baseAmount);

                if ($vipAmount > 0) {
                    $this->createItem($order, null, 'VIP Charge', round($vipAmount, 2));
                }
            }
        });
    }

    private function createItem(Order $order, ?int $pricingRuleId, string $label, float $amount): void
    {
        $item = new OrderPriceItem([
            'order_id' => $order->id,
            'pricing_rule_id' => $pricingRuleId,
            'label' => $label,
            'amount' => $amount,
        ]);

        $item->created_at = $order->created_at;
        $item->save();
    }
}