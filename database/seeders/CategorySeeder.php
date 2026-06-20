<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Car Wash', 'name_ar' => 'غسيل سيارات', 'description' => 'تنظيف وتلميع السيارات الداخلي والخارجي', 'is_active' => true],
            ['name' => 'Maintenance', 'name_ar' => 'صيانة ميكانيكية', 'description' => 'خدمات الصيانة السريعة والميكانيكا العامة', 'is_active' => true],
            ['name' => 'Roadside Assistance', 'name_ar' => 'المساعدة على الطريق', 'description' => 'خدمات الطوارئ والإنقاذ أثناء السير', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert(array_merge($category, [
                'created_at' => now(),
            ]));
        }
    }
}
