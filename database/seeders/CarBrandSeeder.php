<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'Toyota', 'logo' => 'logos/toyota.png', 'is_active' => true],
            ['name' => 'Hyundai', 'logo' => 'logos/hyundai.png', 'is_active' => true],
            ['name' => 'Ford', 'logo' => 'logos/ford.png', 'is_active' => true],
            ['name' => 'Chevrolet', 'logo' => 'logos/chevrolet.png', 'is_active' => true],
            ['name' => 'Nissan', 'logo' => 'logos/nissan.png', 'is_active' => true],
            ['name' => 'Kia', 'logo' => 'logos/kia.png', 'is_active' => true],
            ['name' => 'Mercedes-Benz', 'logo' => 'logos/mercedes.png', 'is_active' => true],
            ['name' => 'BMW', 'logo' => 'logos/bmw.png', 'is_active' => true],
            ['name' => 'Lexus', 'logo' => 'logos/lexus.png', 'is_active' => true],
            ['name' => 'Honda', 'logo' => 'logos/honda.png', 'is_active' => true],
            ['name' => 'Mazda', 'logo' => 'logos/mazda.png', 'is_active' => true],
            ['name' => 'GMC', 'logo' => 'logos/gmc.png', 'is_active' => true],
            ['name' => 'Changan', 'logo' => 'logos/changan.png', 'is_active' => true],
            ['name' => 'Tesla', 'logo' => 'logos/tesla.png', 'is_active' => true],
        ];

        foreach ($brands as $brand) {
            DB::table('car_brands')->insert([
                'name' => $brand['name'],
                'logo' => $brand['logo'],
                'is_active' => $brand['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
