<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubServiceSeeder extends Seeder
{
    public function run(): void
    {
        $exteriorWashId      = DB::table('services')->where('name', 'Exterior Wash')->value('id');
        $interiorCleaningId  = DB::table('services')->where('name', 'Interior Cleaning')->value('id');
        $oilChangeId         = DB::table('services')->where('name', 'Oil Change')->value('id');

        $subServices = [
            ['service_id' => $exteriorWashId, 'name' => 'Tire Shine', 'name_ar' => 'تلميع كفرات', 'description' => 'تلميع الإطارات بمواد خاصة', 'price' => 10, 'is_active' => true],
            ['service_id' => $exteriorWashId, 'name' => 'Window Cleaning', 'name_ar' => 'تنظيف زجاج', 'description' => 'تنظيف الزجاج الخارجي', 'price' => 5, 'is_active' => true],
            ['service_id' => $interiorCleaningId, 'name' => 'Seat Shampoo', 'name_ar' => 'غسيل مقاعد', 'description' => 'غسيل وتعقيم المقاعد القماشية', 'price' => 30, 'is_active' => true],
            ['service_id' => $oilChangeId, 'name' => 'Oil Filter Replacement', 'name_ar' => 'تغيير فلتر زيت', 'description' => 'استبدال فلتر الزيت', 'price' => 20, 'is_active' => true],
        ];

        foreach ($subServices as $subService) {
            DB::table('sub_services')->insert(array_merge($subService, [
                'created_at' => now(),
            ]));
        }
    }
}

