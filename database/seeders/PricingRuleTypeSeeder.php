<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PricingRuleTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['id' => 1, 'name' => 'Peak Hours Surge', 'name_ar' => 'زيادة أوقات الذروة'],
            ['id' => 2, 'name' => 'Distance Extra Fee', 'name_ar' => 'رسوم المسافة الإضافية'],
            ['id' => 3, 'name' => 'Seasonal Discount', 'name_ar' => 'خصم موسمي / أعياد'],
            ['id' => 4, 'name' => 'VIP Car Multiplier', 'name_ar' => 'مُضاعف السيارات الفارهة'],
        ];

        foreach ($types as $type) {
            DB::table('pricing_rule_types')->updateOrInsert(['id' => $type['id']], $type);
        }
    }
}
