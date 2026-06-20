<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Litre', 'name_ar' => 'لتر', 'is_decimal' => true],
            ['name' => 'Piece', 'name_ar' => 'قطعة', 'is_decimal' => false],
            ['name' => 'Kilogram', 'name_ar' => 'كيلوجرام', 'is_decimal' => true],
            ['name' => 'Box', 'name_ar' => 'صندوق', 'is_decimal' => false],
        ];

        foreach ($units as $unit) {
            DB::table('material_units')->insert(array_merge($unit, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
