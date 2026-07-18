<?php

namespace Database\Factories;

use App\Enums\CarEnums\FuelType;
use App\Models\Car;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class CarFactory extends Factory
{
    protected $model = Car::class;

    public function definition(): array
    {

        $brandId = DB::table('car_brands')->inRandomOrder()->first()?->id ?? 1;
        $carTypeId = DB::table('car_types')->inRandomOrder()->first()?->id ?? 1;
        $branchId = DB::table('branches')->inRandomOrder()->first()?->id;
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'brand_id' => $brandId,
            'car_type_id' => $carTypeId,
            'branch_id' => $branchId,
            'plate_number' => $this->faker->bothify('???-####'),
            'model' => $this->faker->randomElement(['Camry', 'Elantra', 'Accent', 'Sonata', 'Land Cruiser', 'Model Y']),
            'year' => $this->faker->year(),
            'color' => $this->faker->safeColorName(),
            'fuel_type' => $this->faker->randomElement(FuelType::values()),
            'cylinders' => $this->faker->randomElement([4, 6, 8]),
            'mileage' => $this->faker->numberBetween(10000, 200000),
            'image_url' => $this->faker->imageUrl(400, 300, 'transport'),
            'is_active' => true,
        ];
    }
}
