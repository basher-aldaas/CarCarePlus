<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'name' => $this->faker->company(),
            'name_ar' => 'شركة ' . $this->faker->word(),
            'commercial_reg' => $this->faker->numerify('##########'),
            'tax_number' => $this->faker->numerify('15###########'),
            'address' => $this->faker->address(),
            'is_active' => 1,
        ];
    }
}
