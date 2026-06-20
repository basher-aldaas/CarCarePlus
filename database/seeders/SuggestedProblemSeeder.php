<?php

namespace Database\Seeders;

use App\Enums\SuggestedProblemCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuggestedProblemSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('suggested_problems')->insert([
            [
                'name' => 'Flat Tire',
                'name_ar' => 'بنشر / إطار مثقوب',
                'description' => 'The car tire is punctured and needs replacement or patching.',
                'category' => SuggestedProblemCategory::TIRES->value, // حسب الـ ENUM عندك
                'created_at' => now(),
            ],
            [
                'name' => 'Engine Overheating',
                'name_ar' => 'ارتفاع حرارة المحرك',
                'description' => 'Temperature gauge is in the red zone or steam is coming from the hood.',
                'category' => SuggestedProblemCategory::MECHANICAL->value,
                'created_at' => now(),
            ],
            [
                'name' => 'Locked Out of Car',
                'name_ar' => 'المفاتيح داخل السيارة',
                'description' => 'Keys are locked inside the vehicle and need professional unlocking.',
                'category' => SuggestedProblemCategory::LOCKSMITH->value,
                'created_at' => now(),
            ]
        ]);
    }
}
