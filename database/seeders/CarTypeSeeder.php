<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Sedan', 'name_ar' => 'سيدان', 'price_multiplier' => 1.0, 'is_active' => true],
            ['name' => 'SUV', 'name_ar' => 'سيارة عائلية / جيب', 'price_multiplier' => 1.2, 'is_active' => true],
            ['name' => 'Truck / Pickup', 'name_ar' => 'بيك أب / شاحنة', 'price_multiplier' => 1.4, 'is_active' => true],
            ['name' => 'Luxury / Sports', 'name_ar' => 'رياضية / فاخرة', 'price_multiplier' => 1.5, 'is_active' => true],
        ];

        foreach ($types as $type) {
            DB::table('car_types')->insert(array_merge($type, [
                'created_at' => now(),
            ]));
        }
    }
}
