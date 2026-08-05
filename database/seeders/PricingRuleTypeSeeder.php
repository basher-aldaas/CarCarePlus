<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PricingRuleTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['id' => 1, 'name' => 'vehicle_type',    'name_ar' => 'نوع السيارة'],
            ['id' => 2, 'name' => 'service',         'name_ar' => 'نوع الخدمة'],
            ['id' => 3, 'name' => 'sub_service',     'name_ar' => 'الخدمة الفرعية'],
            ['id' => 4, 'name' => 'customer_type',   'name_ar' => 'نوع العميل'],
            ['id' => 5, 'name' => 'package',         'name_ar' => 'الباقة'],
            ['id' => 6, 'name' => 'branch',          'name_ar' => 'الفرع'],
            ['id' => 7, 'name' => 'day_of_week',     'name_ar' => 'يوم الأسبوع'],
            ['id' => 8, 'name' => 'time_period',     'name_ar' => 'الفترة الزمنية'],
            ['id' => 9, 'name' => 'season',           'name_ar' => 'الموسم'],
            ['id' => 10, 'name' => 'booking_type',   'name_ar' => 'نوع الحجز'],
            ['id' => 11, 'name' => 'distance',       'name_ar' => 'مسافة إضافية'],
            ['id' => 12, 'name' => 'off_hours',      'name_ar' => 'خارج أوقات العمل'],
        ];
        foreach ($types as $type) {
            DB::table('pricing_rule_types')->updateOrInsert(['id' => $type['id']], $type);
        }
    }
}
