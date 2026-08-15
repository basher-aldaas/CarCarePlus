<?php

namespace Database\Seeders;

use App\Enums\OrderEnums\OrderStatus;
use App\Enums\SparePartRequestStatus;
use App\Models\Material;
use App\Models\Order;
use App\Models\SparePartRequest;
use Illuminate\Database\Seeder;

/**
 * Seeds spare part requests. A mechanic assigned to an order requests a catalog
 * material for that order; the request's status / decided_at are derived from
 * the order's own lifecycle so a "decided" request never sits on an order that
 * is still pending, and pending requests stay on live (in-progress) orders.
 */
class SparePartRequestSeeder extends Seeder
{
    public function run(): void
    {
        if (! Material::query()->where('is_active', true)->exists()) {
            return;
        }

        // Only orders that actually have a mechanic assigned can carry a spare
        // part request — the employee raising it must belong to the order.
        Order::query()
            ->whereNotNull('employee_id')
            ->chunk(50, function ($orders) {
                foreach ($orders as $order) {
                    // Leave ~30% of eligible orders without a request.
                    if (! fake()->boolean(70)) {
                        continue;
                    }

                    $materials = Material::where('is_active', true)
                        ->inRandomOrder()
                        ->limit(fake()->numberBetween(1, 2))
                        ->get();

                    foreach ($materials as $material) {
                        [$status, $decidedAt] = $this->resolveStatus($order);

                        $request = new SparePartRequest([
                            'order_id' => $order->id,
                            'employee_id' => $order->employee_id,
                            'material_id' => $material->id,
                            'quantity' => fake()->numberBetween(1, 4),
                            'specifications' => fake()->boolean(70) ? fake()->sentence() : null,
                            'status' => $status->value,
                            'notes' => $this->resolveNotes($status),
                            'decided_at' => $decidedAt,
                        ]);

                        $request->created_at = $order->created_at;
                        $request->save();
                    }
                }
            });
    }

    /**
     * Derive a plausible status + decision timestamp from the order state.
     *
     * @return array{0: SparePartRequestStatus, 1: ?\DateTimeInterface}
     */
    private function resolveStatus(Order $order): array
    {
        $decidedAt = fn (): \DateTimeInterface => fake()->dateTimeBetween(
            $order->assigned_at ?? $order->created_at,
            $order->completed_at ?? 'now'
        );

        return match ($order->status) {
            OrderStatus::COMPLETED => fake()->randomElement([
                [SparePartRequestStatus::RECEIVED, $decidedAt()],
                [SparePartRequestStatus::ORDERED, $decidedAt()],
                [SparePartRequestStatus::APPROVED, $decidedAt()],
            ]),
            OrderStatus::IN_PROGRESS => fake()->boolean(60)
                ? [SparePartRequestStatus::APPROVED, $decidedAt()]
                : [SparePartRequestStatus::PENDING, null],
            OrderStatus::CANCELLED => fake()->boolean(50)
                ? [SparePartRequestStatus::REJECTED, $decidedAt()]
                : [SparePartRequestStatus::PENDING, null],
            default => [SparePartRequestStatus::PENDING, null],
        };
    }

    private function resolveNotes(SparePartRequestStatus $status): ?string
    {
        return match ($status) {
            SparePartRequestStatus::REJECTED => fake()->sentence(),
            SparePartRequestStatus::PENDING => null,
            default => fake()->boolean(50) ? fake()->sentence() : null,
        };
    }
}