<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\User;
use App\Models\Company;
use App\Models\CarType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class CarFactory extends Factory
{
    protected $model = Car::class;

    public function definition(): array
    {

        $brandName = DB::table('car_brands')->inRandomOrder()->first()?->name ?? 'Toyota';
        $carTypeId = DB::table('car_types')->inRandomOrder()->first()?->id ?? 1;
        return [
            'costumer_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'company_id' => $this->faker->boolean(30) ? (Company::inRandomOrder()->first()?->id ?? Company::factory()) : null, // 30% للشركات
            'car_type_id' => $carTypeId,
            'plate_number' => $this->faker->bothify('???-####'),
            'brand' => $brandName,
            'model' => $this->faker->randomElement(['Camry', 'Elantra', 'Accent', 'Sonata', 'Land Cruiser', 'Model Y']),
            'year' => $this->faker->year(),
            'color' => $this->faker->safeColorName(),
            'fuel_type' => $this->faker->randomElement(['Gasoline', 'Diesel', 'Electric', 'Hybrid']),
            'cylinders' => $this->faker->randomElement([4, 6, 8]),
            'mileage' => $this->faker->numberBetween(10000, 200000),
            'image_url' => $this->faker->imageUrl(400, 300, 'transport'),
            'is_active' => true,
        ];
    }
}
