<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        // جلب حساب مدير الفرع المجهز مسبقاً لربطه بالفرع
        $branchAdmin = DB::table('users')->where('email', 'admin@system.com')->first();

        if ($branchAdmin) {
            DB::table('branches')->updateOrInsert(
                ['id' => 1], // لمنع التكرار في حال إعادة التشغيل
                [
                    'admin_id' => $branchAdmin->id,
                    'name' => 'Main Riyadh Branch',
                    'name_ar' => 'فرع الرياض الرئيسي',
                    'city' => 'Riyadh',
                    'address' => 'طريق الملك فهد، العليا', // مطابقة لاسم الحقل addres في الـ ERD
                    'latitude' => 24.713600,
                    'longitude' => 46.675300,
                    'phone' => '0511111111',
                    'is_active' => true,
                    'working_hours' => json_encode(['start' => '08:00', 'end' => '23:00']),
                    'is_24h' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
