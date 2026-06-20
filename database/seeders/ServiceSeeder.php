<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // جلب معرفات التصنيفات والأنواع لربطها
        $washCategory = DB::table('categories')->where('name', 'Car Wash')->first();
        $maintenanceCategory = DB::table('categories')->where('name', 'Maintenance')->first();

        $washType = DB::table('service_types')->where('name', 'Full Wash')->first();
        $washType2 = DB::table('service_types')->where('name', 'Polishing & Wax')->first();

        $maintenanceType = DB::table('service_types')->where('name', 'Oil Change')->first();

        $services = [
            // خدمة 1
            [
                'id' => 1, // تثبيت المعرفات يسهل عليك ربط الخدمات الفرعية لاحقاً
                'category_id' => $washCategory?->id,
                'service_type_id' => $washType?->id,
                'name' => 'Eco Friendly Full Wash',
                'name_ar' => 'غسيل كامل صديق للبيئة',
                'description' => 'غسيل خارجي وداخلي لكامل السيارة',
                'base_price' => 120.00,
                'is_vip_available' => true,
                'vip_extra_price' => 50.00,
                'duration_minutes' => 45,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // خدمة 2
            [
                'id' => 2,
                'category_id' => $washCategory?->id,
                'service_type_id' => $washType2?->id,
                'name' => 'Premium Waxing & Polish',
                'name_ar' => 'تلميع وبوليش نانو واكس',
                'description' => 'إزالة الخدوش السطحية وإضافة طبقة حماية ولمعان لهيكل السيارة',
                'base_price' => 350.00,
                'is_vip_available' => false,
                'vip_extra_price' => null,
                'duration_minutes' => 120,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // خدمة 3
            [
                'id' => 3,
                'category_id' => $maintenanceCategory?->id,
                'service_type_id' => $maintenanceType?->id,
                'name' => 'Oil & Filter Change Service',
                'name_ar' => 'خدمة تغيير الزيت والفلتر',
                'description' => 'تغيير زيت المحرك ',
                'base_price' => 80.00,
                'is_vip_available' => true,
                'vip_extra_price' => 30.00,
                'duration_minutes' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($services as $service) {
            DB::table('services')->updateOrInsert(['id' => $service['id']], $service);
        }
    }
}
