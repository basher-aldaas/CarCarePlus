<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageServiceSeeder extends Seeder
{
    public function run(): void
    {
        // افترضنا أن الـ IDs للباقات والخدمات تبدأ من 1 بعد تشغيل السيرفرات السابقة
        DB::table('package_services')->insert([
            [
                'package_id' => 1,      // باقة غسيل شهري مثلاً
                'service_id' => 1,      // خدمة غسيل خارجي
                'allowed_count' => 4,   // مسموح 4 مرات في الشهر
            ]
        ]);
    }
}
