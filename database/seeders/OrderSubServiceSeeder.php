<?php

namespace Database\Seeders;

use App\Enums\OrderEnums\OrderStatus;
use App\Enums\OrderEnums\OrderSubServiceStatus;
use App\Models\Order;
use App\Models\OrderSubService;
use App\Models\SubService;
use Illuminate\Database\Seeder;

/**
 * For every seeded order, attaches 1-3 sub-services that actually belong to
 * that order's service, with a status/checked_at derived from the order's
 * own status and timeline so the two tables never disagree with each other.
 */
class OrderSubServiceSeeder extends Seeder
{
    public function run(): void
    {
        Order::whereNotNull('service_id')->chunk(50, function ($orders) {
            foreach ($orders as $order) {
                $subServices = SubService::where('service_id', $order->service_id)
                    ->inRandomOrder()
                    ->limit(fake()->numberBetween(1, 3))
                    ->get();

                foreach ($subServices as $subService) {
                    [$status, $checkedAt] = $this->resolveStatus($order);

                    $orderSubService = new OrderSubService([
                        'order_id' => $order->id,
                        'sub_service_id' => $subService->id,
                        'price' => $subService->price,
                        'status' => $status->value,
                        'notes' => fake()->boolean(20) ? fake()->sentence() : null,
                        'checked_at' => $checkedAt,
                    ]);

                    $orderSubService->created_at = $order->created_at;
                    $orderSubService->updated_at = $checkedAt ?? $order->created_at;
                    $orderSubService->save();
                }
            }
        });
    }

    /**
     * @return array{0: OrderSubServiceStatus, 1: ?\DateTimeInterface}
     */
    private function resolveStatus(Order $order): array
    {
        return match ($order->status) {
            OrderStatus::COMPLETED => fake()->boolean(85)
                ? [OrderSubServiceStatus::DONE, fake()->dateTimeBetween($order->started_at ?? $order->created_at, $order->completed_at ?? 'now')]
                : [OrderSubServiceStatus::SKIPPED, null],
            OrderStatus::IN_PROGRESS => fake()->boolean(50)
                ? [OrderSubServiceStatus::DONE, fake()->dateTimeBetween($order->started_at ?? $order->created_at, 'now')]
                : [OrderSubServiceStatus::PENDING, null],
            OrderStatus::CANCELLED => fake()->boolean(70)
                ? [OrderSubServiceStatus::SKIPPED, null]
                : [OrderSubServiceStatus::PENDING, null],
            default => [OrderSubServiceStatus::PENDING, null],
        };
    }
}