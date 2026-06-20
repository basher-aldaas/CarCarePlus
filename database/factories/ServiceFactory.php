<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Category;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'service_type_id' => ServiceType::factory(),
            'name' => $this->faker->word() . ' Service',
            'name_ar' => 'خدمة ' . $this->faker->word(),
            'description' => $this->faker->sentence(),
            'base_price' => $this->faker->randomFloat(2, 50, 500),
            'is_vip_available' => $this->faker->boolean(),
            'vip_extra_price' => $this->faker->boolean() ? $this->faker->randomFloat(2, 20, 100) : null,
            'duration_minutes' => $this->faker->randomElement([30, 45, 60, 90, 120]),
        ];
    }
}
