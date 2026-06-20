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

            $basePrice = $this->faker->randomElement([50, 100, 150, 200]);
        $isVip = $this->faker->boolean(20); // 20% طلبات VIP
        $vipPrice = $isVip ? 50.00 : null;

        // جلب علاقات عشوائية من قاعدة البيانات
        $branchId = DB::table('branches')->inRandomOrder()->first()?->id;
        $employeeId = DB::table('employees')->inRandomOrder()->first()?->id;
        $serviceId = DB::table('services')->inRandomOrder()->first()?->id;

        return [
            'customer_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'company_id' => $this->faker->boolean(20) ? (Company::inRandomOrder()->first()?->id ?? Company::factory()) : null,
            'car_id' => Car::inRandomOrder()->first()?->id ?? Car::factory(),
            'branch_id' => $branchId,
            'employee_id' => $employeeId,
            'service_id' => $serviceId,
            'type' => $this->faker->randomElement(['wash', 'maintenance', 'road_assistance']), // حسب الـ ENUM عندك
            'booking_type' => $this->faker->randomElement(['immed', 'sched']),
            'is_vip' => $isVip,
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+1 week'),
            'started_at' => now(),
            'completed_at' => now()->addMinutes($this->faker->numberBetween(30, 90)),
            'cancelled_at' => null,
            'cancel_reason' => null,
            'location_lat' => $this->faker->latitude(24.0, 25.0),
            'location_lng' => $this->faker->longitude(46.0, 47.0),
            'location_address' => $this->faker->address(),
            'distance_km' => $this->faker->randomFloat(2, 1, 30),
            'base_price' => $basePrice,
            'vip_price' => $vipPrice,
            'distance_price' => $this->faker->randomFloat(2, 0, 25),
            'sub_services_price' => $this->faker->randomFloat(2, 0, 100),
            'order_material_price' => $this->faker->randomFloat(2, 0, 150),
            'discount_amount' => 0.00,
            'total_price' => $basePrice + ($vipPrice ?? 0),
            'notes' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pending', 'assigned', 'processing', 'completed']), // الـ ENUM الخاص بك            'assigned_at' => $this->faker->dateTimeThisMonth(),
        ];
    }
}
