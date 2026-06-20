<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $carWashId    = DB::table('categories')->where('name', 'Car Wash')->value('id');
        $maintenanceId = DB::table('categories')->where('name', 'Maintenance')->value('id');
        $roadsideId    = DB::table('categories')->where('name', 'Roadside Assistance')->value('id');

        $services = [
            ['category_id' => $carWashId, 'name' => 'Exterior Wash', 'name_ar' => 'غسيل خارجي', 'description' => 'غسيل خارجي شامل للسيارة', 'base_price' => 50, 'is_vip_available' => true, 'vip_extra_price' => 20, 'duration_minutes' => 30],
            ['category_id' => $carWashId, 'name' => 'Interior Cleaning', 'name_ar' => 'تنظيف داخلي', 'description' => 'تنظيف داخلي شامل للمقاعد والسجاد', 'base_price' => 70, 'is_vip_available' => true, 'vip_extra_price' => 25, 'duration_minutes' => 45],
            ['category_id' => $maintenanceId, 'name' => 'Oil Change', 'name_ar' => 'تغيير زيت', 'description' => 'تغيير زيت المحرك والفلتر', 'base_price' => 120, 'is_vip_available' => false, 'vip_extra_price' => null, 'duration_minutes' => 45],
            ['category_id' => $maintenanceId, 'name' => 'Brake Inspection', 'name_ar' => 'فحص فرامل', 'description' => 'فحص وصيانة نظام الفرامل', 'base_price' => 90, 'is_vip_available' => false, 'vip_extra_price' => null, 'duration_minutes' => 40],
            ['category_id' => $roadsideId, 'name' => 'Battery Jump Start', 'name_ar' => 'تشغيل بطارية', 'description' => 'تشغيل البطارية للسيارات العالقة', 'base_price' => 80, 'is_vip_available' => false, 'vip_extra_price' => null, 'duration_minutes' => 20],
            ['category_id' => $roadsideId, 'name' => 'Flat Tire Change', 'name_ar' => 'تغيير إطار', 'description' => 'تغيير الإطار المثقوب على الطريق', 'base_price' => 60, 'is_vip_available' => false, 'vip_extra_price' => null, 'duration_minutes' => 25],
        ];

        foreach ($services as $service) {
            DB::table('services')->insert(array_merge($service, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
