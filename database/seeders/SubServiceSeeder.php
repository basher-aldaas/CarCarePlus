<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubServiceSeeder extends Seeder
{
    public function run(): void
    {
        $subServices = [
            // خدمات فرعية تابعة للخدمة رقم 1 (Eco Friendly Full Wash)
            [
                'service_id' => 1,
                'name' => 'Engine Bay Detailing',
                'name_ar' => 'تلميع وتنظيف المحرك',
                'description' => 'تنظيف المحرك بمواد خاصة آمنة على التوصيلات الكهربائية',
                'price' => 40.00,
                'is_active' => true,
                'created_at' => now(),
            ],
            [
                'service_id' => 1,
                'name' => 'Leather Conditioning',
                'name_ar' => 'ترطيب المقاعد الجلدية',
                'description' => 'حماية المقاعد من التشقق باستخدام مواد إيطالية',
                'price' => 30.00,
                'is_active' => true,
                'created_at' => now(),
            ],

            // خدمات فرعية تابعة للخدمة رقم 2 (Premium Waxing & Polish)
            [
                'service_id' => 2,
                'name' => 'Headlight Restoration',
                'name_ar' => 'تلميع المصابيح الأمامية',
                'description' => 'إزالة الاصفرار وتحسين مدى الرؤية الليلية',
                'price' => 60.00,
                'is_active' => true,
                'created_at' => now(),
            ],
            [
                'service_id' => 2,
                'name' => 'Rims Ceramic Coating',
                'name_ar' => 'حماية الجنوط بالسيراميك',
                'description' => 'طبقة نانو لحماية العجلات من برادة الفرامل والأوساخ',
                'price' => 100.00,
                'is_active' => true,
                'created_at' => now(),
            ],

            // خدمات فرعية تابعة للخدمة رقم 3 (Oil & Filter Change Service)
            [
                'service_id' => 3,
                'name' => 'Air Filter Replacement',
                'name_ar' => 'استبدال فلتر الهواء',
                'description' => 'فك وتركيب فلتر هواء المحرك الجديد',
                'price' => 15.00,
                'is_active' => true,
                'created_at' => now(),
            ],
            [
                'service_id' => 3,
                'name' => 'AC Cabin Filter Replacement',
                'name_ar' => 'استبدال فلتر مكيف السيارة',
                'description' => 'تنظيف المجرى وتغيير فلتر المقصورة الداخلية',
                'price' => 20.00,
                'is_active' => true,
                'created_at' => now(),
            ]
        ];

        foreach ($subServices as $subService) {
            DB::table('sub_services')->insert($subService);
        }
    }
}
