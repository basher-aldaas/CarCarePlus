<?php

namespace Database\Factories;

use App\Enums\OrderEnums\OrderStatus;
use App\Models\Branch;
use App\Models\Car;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $isVip = $this->faker->boolean(20);

        $branch = Branch::inRandomOrder()->first();
        $service = Service::inRandomOrder()->first();
        $employee = $branch
            ? Employee::where('branch_id', $branch->id)->inRandomOrder()->first()
            : Employee::inRandomOrder()->first();

        $status = $this->faker->randomElement(OrderStatus::cases());

        // Anything past "pending" must already have happened, so its
        // scheduled time has to be in the past (otherwise the dependent
        // assigned/started/completed timestamps below would need to sample
        // between a future date and "now", which faker rejects).
        $scheduledAt = $status === OrderStatus::PENDING
            ? $this->faker->dateTimeBetween('now', '+1 week')
            : $this->faker->dateTimeBetween('-2 weeks', '-1 hours');

        $assignedAt = null;
        $startedAt = null;
        $completedAt = null;
        $cancelledAt = null;
        $cancelReason = null;

        if (in_array($status, [OrderStatus::ASSIGNED, OrderStatus::IN_PROGRESS, OrderStatus::COMPLETED], true)) {
            $assignedAt = $this->faker->dateTimeBetween($scheduledAt, 'now');
        }

        if (in_array($status, [OrderStatus::IN_PROGRESS, OrderStatus::COMPLETED], true)) {
            $startedAt = $this->faker->dateTimeBetween($assignedAt, 'now');
        }

        if ($status === OrderStatus::COMPLETED) {
            $completedAt = $this->faker->dateTimeBetween($startedAt, 'now');
        }

        if ($status === OrderStatus::CANCELLED) {
            $cancelledAt = $this->faker->dateTimeBetween($scheduledAt, 'now');
            $cancelReason = $this->faker->sentence();
        }

        $basePrice = (float) ($service->base_price ?? $this->faker->randomElement([50, 100, 150, 200]));
        $vipPrice = $isVip ? (float) ($service->vip_extra_price ?? 50) : 0;

        return [
            'customer_id' => User::role(['customer_personal', 'customer_company'])->inRandomOrder()->first()?->id
                ?? User::factory(),
            'company_id' => $this->faker->boolean(20) ? Company::inRandomOrder()->first()?->id : null,
            'car_id' => Car::inRandomOrder()->first()?->id ?? Car::factory(),
            'branch_id' => $branch?->id,
            'employee_id' => $employee?->id,
            'service_id' => $service?->id,
            'category_id' => $service?->category_id,
            'booking_type' => $this->faker->boolean(), // true = immediate, false = scheduled
            'is_vip' => $isVip,
            'scheduled_at' => $scheduledAt,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'cancelled_at' => $cancelledAt,
            'cancel_reason' => $cancelReason,
            'location_lat' => $this->faker->latitude(24.0, 25.0),
            'location_lng' => $this->faker->longitude(46.0, 47.0),
            'location_address' => $this->faker->address(),
            'distance_km' => $this->faker->randomFloat(2, 1, 30),
            'discount_amount' => 0.00,
            'total_price' => $basePrice + $vipPrice,
            'notes' => $this->faker->sentence(),
            'status' => $status->value,
            'assigned_at' => $assignedAt,
        ];
    }
}