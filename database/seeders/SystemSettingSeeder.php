<?php
namespace Database\Seeders;

use App\Enums\SystemSettingType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'tax_percentage',
                'value' => '15',
                'type' => SystemSettingType::NUMBER->value,
                'description' => 'نسبة ضريبة القيمة المضافة العامة'
            ],
            [
                'key' => 'allow_cash_payment',
                'value' => 'true',
                'type' => SystemSettingType::BOOLEAN->value,
                'description' => 'السماح بالدفع النقدي عند الاستلام في الموقع'
            ],
            [
                'key' => 'free_distance_km',
                'value' => '5',
                'type' => SystemSettingType::NUMBER->value,
                'description' => 'المسافة المجانية المتاحة للموظف المتنقل قبل احتساب رسوم الطريق'
            ],
            [
                'key' => 'vip_discount_percentage',
                'value' => '10',
                'type' => SystemSettingType::NUMBER->value,
                'description' => 'نسبة الخصم لعملاء VIP'
            ],
            [
                'key' => 'minimum_order_amount',
                'value' => '50',
                'type' => SystemSettingType::NUMBER->value,
                'description' => 'الحد الأدنى لقيمة الطلب'
            ],
            [
                'key' => 'support_phone',
                'value' => '+966500000000',
                'type' => SystemSettingType::STRING->value,
                'description' => 'رقم خدمة العملاء'
            ],
            [
                'key' => 'mobile_service_enabled',
                'value' => 'true',
                'type' => SystemSettingType::BOOLEAN->value,
                'description' => 'تفعيل الخدمة المتنقلة'
            ],
            [
                'key' => 'road_assistance_enabled',
                'value' => 'true',
                'type' => SystemSettingType::BOOLEAN->value,
                'description' => 'تفعيل خدمة المساعدة على الطريق'
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(['key' => $setting['key']], $setting);
        }
    }
}
