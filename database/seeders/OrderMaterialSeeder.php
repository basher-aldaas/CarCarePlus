<?php

namespace Database\Seeders;

use App\Enums\OrderEnums\OrderMaterialStatus;
use App\Enums\OrderEnums\OrderStatus;
use App\Models\Material;
use App\Models\Order;
use App\Models\OrderMaterial;
use Illuminate\Database\Seeder;

/**
 * For every seeded order, attaches 1-3 materials consumed on that order,
 * with a status/approved_at derived from the order's own status and
 * timeline so materials never look "used" on an order that's still pending.
 */
class OrderMaterialSeeder extends Seeder
{
    public function run(): void
    {
        if (! Material::query()->where('is_active', true)->exists()) {
            return;
        }

        Order::query()->chunk(50, function ($orders) {
            foreach ($orders as $order) {
                $materials = Material::where('is_active', true)
                    ->inRandomOrder()
                    ->limit(fake()->numberBetween(1, 3))
                    ->get();

                $requestedBy = $order->employee?->user_id ?? $order->customer_id;

                foreach ($materials as $material) {
                    [$status, $approvedAt] = $this->resolveStatus($order);

                    $quantity = fake()->randomFloat(2, 1, 5);
                    $unitPrice = (float) $material->unit_price;

                    $orderMaterial = new OrderMaterial([
                        'order_id' => $order->id,
                        'material_id' => $material->id,
                        'requested_by' => $requestedBy,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => round($quantity * $unitPrice, 2),
                        'status' => $status->value,
                        'approved_at' => $approvedAt,
                    ]);

                    $orderMaterial->created_at = $order->created_at;
                    $orderMaterial->save();
                }
            }
        });
    }

    /**
     * @return array{0: OrderMaterialStatus, 1: ?\DateTimeInterface}
     */
    private function resolveStatus(Order $order): array
    {
        return match ($order->status) {
            OrderStatus::COMPLETED => fake()->boolean(80)
                ? [OrderMaterialStatus::USED, fake()->dateTimeBetween($order->assigned_at ?? $order->created_at, $order->completed_at ?? 'now')]
                : [OrderMaterialStatus::APPROVED, fake()->dateTimeBetween($order->assigned_at ?? $order->created_at, 'now')],
            OrderStatus::IN_PROGRESS => fake()->boolean(50)
                ? [OrderMaterialStatus::USED, fake()->dateTimeBetween($order->started_at ?? $order->created_at, 'now')]
                : [OrderMaterialStatus::APPROVED, fake()->dateTimeBetween($order->assigned_at ?? $order->created_at, 'now')],
            OrderStatus::CANCELLED => fake()->boolean(60)
                ? [OrderMaterialStatus::REJECTED, null]
                : [OrderMaterialStatus::REQUESTED, null],
            default => [OrderMaterialStatus::REQUESTED, null],
        };
    }
}