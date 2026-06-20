<?php

namespace Database\Seeders;

use App\Enums\AiRuleConditionType;
use App\Enums\CarEnums\CarTypeSize;
use App\Enums\CarEnums\FuelType;
use App\Models\Car;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AiRuleSeeder extends Seeder
{
    public function run(): void
    {
        $toyotaId = DB::table('car_brands')
            ->where('name', 'Toyota')
            ->value('id');

        $teslaId = DB::table('car_brands')
            ->where('name', 'Tesla')
            ->value('id');

        DB::table('ai_rules')->insert([
            [
                'name' => 'Battery Drainage Check',
                'name_ar' => 'فحص نفاد البطارية',
                'type' => AiRuleConditionType::DIAGNOSIS->value,
                'condition_key' => 'no_start',
                'condition_value' => 'clicking_sound',
                'brand_id' => $toyotaId,
                'car_type' => CarTypeSize::SMALL->value,
                'fuel_type' => FuelType::PETROL->value,
                'response_template' => 'Based on the clicking sound, your battery might be dead. We recommend dispatching a technician with a battery booster.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'EV Overheating',
                'name_ar' => 'ارتفاع حرارة السيارة الكهربائية',
                'type' => AiRuleConditionType::WARNING->value,
                'condition_key' => 'dashboard_light',
                'condition_value' => 'battery_temp_high',
                'brand_id' => $teslaId,
                'car_type' => CarTypeSize::SUV->value,
                'fuel_type' => FuelType::ELECTRIC->value,
                'response_template' => 'Please pull over safely immediately. EV cooling system warning detected. Towing is highly recommended.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
