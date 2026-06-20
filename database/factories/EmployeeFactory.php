<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'branch_id' => Branch::factory(),
            'type' => $this->faker->randomElement([0, 1]), // 0 technician, 1 washer حسب الـ Type بالـ ERD
            'is_active' => true,
            'rating_avg' => $this->faker->randomFloat(2, 3, 5), // تقييم بين 3 و 5
        ];
    }
}
