<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\User;
use App\Models\Company;
use App\Models\CarType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarFactory extends Factory
{
    protected $model = Car::class;

    public function definition(): array
    {
        return [
            'costumer_id' => User::factory(),
            'company_id' => $this->faker->boolean(30) ? Company::factory() : null, // nullable بنسبة 30% للشركات
            'car_type_id' => CarType::factory(),
            'plate_number' => $this->faker->bothify('??? ####'),
            'brand' => $this->faker->randomElement(['Toyota', 'BMW', 'Mercedes', 'Ford']),
            'model' => $this->faker->word(),
            'year' => $this->faker->year(),
            'color' => $this->faker->safeColorName(),
            'fuel_type' => $this->faker->randomElement(['Gasoline', 'Diesel', 'Electric', 'Hybrid']),
            'cylinders' => $this->faker->randomElement([4, 6, 8]),
            'mileage' => $this->faker->numberBetween(1000, 200000),
            'image_url' => $this->faker->imageUrl(400, 300, 'transport'),
            'is_active' => true,
        ];
    }
}
