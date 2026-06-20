<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProblemTypeSeeder extends Seeder
{
    public function run(): void
    {
        $problems = [
            ['name' => 'Flat Tire', 'name_ar' => 'بنشر / إطار تالف', 'is_active' => true],
            ['name' => 'Dead Battery', 'name_ar' => 'بطارية فارغة', 'is_active' => true],
            ['name' => 'Engine Overheating', 'name_ar' => 'ارتفاع حرارة المحرك', 'is_active' => true],
            ['name' => 'Out of Fuel', 'name_ar' => 'نفاد الوقود', 'is_active' => true],
            ['name' => 'Brake Failure', 'name_ar' => 'عطل في الفرامل', 'is_active' => true],
        ];

        foreach ($problems as $problem) {
            DB::table('problem_types')->insert($problem);
        }
    }
}
