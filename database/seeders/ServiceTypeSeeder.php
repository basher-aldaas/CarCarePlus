<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [

            // Car Wash
            ['name' => 'Exterior Wash', 'name_ar' => 'غسيل خارجي'],
            ['name' => 'Interior Wash', 'name_ar' => 'غسيل داخلي'],
            ['name' => 'Full Wash', 'name_ar' => 'غسيل كامل'],
            ['name' => 'Steam Wash', 'name_ar' => 'غسيل بالبخار'],
            ['name' => 'Polishing & Wax', 'name_ar' => 'تلميع وتلميع شمعي'],

            // Car Maintenance
            ['name' => 'Oil Change', 'name_ar' => 'تغيير زيت'],
            ['name' => 'Brake Service', 'name_ar' => 'صيانة الفرامل'],
            ['name' => 'Battery Service', 'name_ar' => 'صيانة البطارية'],
            ['name' => 'Tire Service', 'name_ar' => 'صيانة الإطارات'],
            ['name' => 'Engine Check', 'name_ar' => 'فحص المحرك'],

            // Road Assistance
            ['name' => 'Battery Jump Start', 'name_ar' => 'تشغيل البطارية'],
            ['name' => 'Tire Replacement', 'name_ar' => 'تبديل إطار'],
            ['name' => 'Fuel Delivery', 'name_ar' => 'توصيل وقود'],
            ['name' => 'Vehicle Towing', 'name_ar' => 'سحب المركبة'],
            ['name' => 'Lockout Assistance', 'name_ar' => 'فتح المركبة المقفلة'],
        ];

        foreach ($types as $type) {
            DB::table('service_types')->insert([
                'name' => $type['name'],
                'name_ar' => $type['name_ar'],
                'created_at' => now(),
            ]);
        }
    }
}
