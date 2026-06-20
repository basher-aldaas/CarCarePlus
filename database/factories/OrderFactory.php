<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Company;
use App\Models\Car;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $basePrice = $this->faker->randomFloat(2, 50, 300);
        $subServicesPrice = $this->faker->randomFloat(2, 10, 100);

        return [
            'customer_id' => User::factory(),
            'company_id' => $this->faker->boolean(20) ? Company::factory() : null,
            'car_id' => Car::factory(),
            'branch_id' => Branch::factory(),
            'employee_id' => Employee::factory(),
            'service_id' => Service::factory(),
            'type' => $this->faker->randomElement(['Standard', 'VIP']),
            'booking_type' => $this->faker->randomElement([0, 1]), // immed=0, sched=1
            'is_vip' => $this->faker->boolean(),
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+1 week'),
            'started_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
            'location_lat' => $this->faker->latitude(),
            'location_lng' => $this->faker->longitude(),
            'location_address' => $this->faker->address(),
            'distance_km' => $this->faker->randomFloat(2, 1, 30),
            'base_price' => $basePrice,
            'sub_services_price' => $subServicesPrice,
            'total_price' => $basePrice + $subServicesPrice,
            'status' => $this->faker->randomElement(['pending', 'assigned', 'processing', 'completed', 'cancelled']),
            'assigned_at' => $this->faker->dateTimeThisMonth(),
        ];
    }
}
