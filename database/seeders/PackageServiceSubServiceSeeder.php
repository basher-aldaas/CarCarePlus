<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageServiceSubServiceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('package_service_sub_services')->insert([
            [
                'package_service_id' => 1, // مرتبط بالسجل الأول من جدول package_services أعلاه
                'sub_service_id' => 1,     // خدمة فرعية (مثلاً: تلميع إطارات)
                'price_override' => 0.00,  // مجانية تماماً داخل هذه الباقة
                'is_active' => true,
                'created_at' => now(),
            ],
            [
                'package_service_id' => 2,
                'sub_service_id' => 2,     // خدمة فرعية (مثلاً: تنظيف فلاتر)
                'price_override' => 15.50, // سعر خاص ومخفض بدلاً من السعر الأصلي
                'is_active' => true,
                'created_at' => now(),
            ]
        ]);
    }
}
