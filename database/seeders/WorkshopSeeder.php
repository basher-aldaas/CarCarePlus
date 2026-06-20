<?php

namespace Database\Seeders;

use App\Enums\WorkshopStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkshopSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('workshops')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'Al-Tamyoz Workshop',
                'name_ar' => 'مركز صيانة التميز',
                'phone' => '0522222222',
                'address' => 'المنطقة الصناعية، مخرج 18',
                'city' => 'Riyadh',
                'latitude' => 24.633300,
                'longitude' => 46.716700,
                'status' => WorkshopStatus::ACTIVE->value, // متوافق مع نوع الـ ENUM في الـ ERD
                'rating_avg' => 4.80,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
