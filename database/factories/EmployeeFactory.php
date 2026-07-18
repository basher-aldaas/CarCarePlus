<?php

namespace Database\Factories;

use App\Enums\EmployeeType;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'branch_id' => Branch::factory(),
            'type' => $this->faker->randomElement([EmployeeType::WASHER, EmployeeType::MECHANIC]),
            'is_active' => true,
            'rating_avg' => $this->faker->randomFloat(2, 3, 5), // تقييم بين 3 و 5
        ];
    }
}
