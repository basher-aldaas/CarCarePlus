<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'admin_id' => User::factory(),
            'name' => $this->faker->city() . ' Branch',
            'name_ar' => 'فرع ' . $this->faker->word(),
            'city' => $this->faker->city(),
            'addres' => $this->faker->address(), // مطابقة لـ "addres" في الداتا
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'phone' => $this->faker->phoneNumber(),
            'is_active' => true,
            'working_hours' => json_encode(['mon' => '08:00-20:00', 'tue' => '08:00-20:00']),
            'is_24h' => false,
        ];
    }
}
