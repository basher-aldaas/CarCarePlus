<?php

namespace Database\Seeders;

use App\Enums\OrderEnums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Database\Seeder;

/**
 * Rebuilds the transition chain (pending -> assigned -> in_progress ->
 * completed, or pending -> cancelled) that each seeded order must have gone
 * through to reach its current status, using that order's own
 * assigned_at/started_at/completed_at/cancelled_at as the log timestamps.
 */
class OrderStatusHistorySeeder extends Seeder
{
    public function run(): void
    {
        Order::query()->chunk(50, function ($orders) {
            foreach ($orders as $order) {
                foreach ($this->transitionsFor($order) as [$from, $to, $at]) {
                    if (! $at) {
                        continue;
                    }

                    $history = new OrderStatusHistory([
                        'order_id' => $order->id,
                        'employee_id' => $order->employee_id,
                        'from_status' => $from,
                        'to_status' => $to,
                        'note' => $to === OrderStatus::CANCELLED->value ? $order->cancel_reason : null,
                    ]);

                    $history->created_at = $at;
                    $history->save();
                }
            }
        });
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: ?\DateTimeInterface}>
     */
    private function transitionsFor(Order $order): array
    {
        if ($order->status === OrderStatus::CANCELLED) {
            return [
                [OrderStatus::PENDING->value, OrderStatus::CANCELLED->value, $order->cancelled_at],
            ];
        }

        $chain = [
            [OrderStatus::PENDING->value, OrderStatus::ASSIGNED->value, $order->assigned_at],
            [OrderStatus::ASSIGNED->value, OrderStatus::IN_PROGRESS->value, $order->started_at],
            [OrderStatus::IN_PROGRESS->value, OrderStatus::COMPLETED->value, $order->completed_at],
        ];

        $reachedIndex = match ($order->status) {
            OrderStatus::ASSIGNED => 0,
            OrderStatus::IN_PROGRESS => 1,
            OrderStatus::COMPLETED => 2,
            default => -1, // still PENDING, no transitions yet
        };

        return array_slice($chain, 0, $reachedIndex + 1);
    }
}