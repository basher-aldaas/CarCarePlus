<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        // ربط جميع الفروع بالمدير العام (Super Admin)
        $superAdmin = DB::table('users')->where('email', 'superadmin@system.com')->first();

        if (! $superAdmin) {
            return;
        }

        $branches = [
            [
                'name' => 'Main Riyadh Branch',
                'name_ar' => 'فرع الرياض الرئيسي',
                'city' => 'Riyadh',
                'address' => 'طريق الملك فهد، العليا',
                'latitude' => 24.713600,
                'longitude' => 46.675300,
                'phone' => '0511111111',
                'is_24h' => false,
            ],
            [
                'name' => 'Jeddah Branch',
                'name_ar' => 'فرع جدة',
                'city' => 'Jeddah',
                'address' => 'شارع التحلية، الأندلس',
                'latitude' => 21.543300,
                'longitude' => 39.172800,
                'phone' => '0512222222',
                'is_24h' => true,
            ],
            [
                'name' => 'Dammam Branch',
                'name_ar' => 'فرع الدمام',
                'city' => 'Dammam',
                'address' => 'طريق الملك سعود، الشاطئ',
                'latitude' => 26.420700,
                'longitude' => 50.088800,
                'phone' => '0513333333',
                'is_24h' => false,
            ],
            [
                'name' => 'Makkah Branch',
                'name_ar' => 'فرع مكة المكرمة',
                'city' => 'Makkah',
                'address' => 'طريق العزيزية، العزيزية',
                'latitude' => 21.389100,
                'longitude' => 39.857900,
                'phone' => '0514444444',
                'is_24h' => false,
            ],
            [
                'name' => 'Madinah Branch',
                'name_ar' => 'فرع المدينة المنورة',
                'city' => 'Madinah',
                'address' => 'طريق قباء، قباء',
                'latitude' => 24.470900,
                'longitude' => 39.611200,
                'phone' => '0515555555',
                'is_24h' => true,
            ],
        ];

        foreach ($branches as $branch) {
            DB::table('branches')->updateOrInsert(
                ['name' => $branch['name']],
                array_merge($branch, [
                    'admin_id' => $superAdmin->id,
                    'is_active' => true,
                    'working_hours' => json_encode(['start' => '08:00', 'end' => '23:00']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}