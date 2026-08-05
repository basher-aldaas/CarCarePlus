<?php

namespace Database\Seeders;

use App\Models\PricingRule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PricingRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            [
                'id' => 1,
                'pricing_rule_type_id' => 1,
                'name' => 'SUV Extra Charge',
                'name_ar' => 'زيادة سعر سيارات SUV',
                'value' => 20,
                'conditions' => [
                    'vehicle_type' => 'SUV',
                ],
                'is_active' => true,
            ],

            [
                'id' => 2,
                'pricing_rule_type_id' => 1,
                'name' => 'Luxury Vehicle Extra Charge',
                'name_ar' => 'زيادة سعر السيارات الفاخرة',
                'value' => 30,
                'conditions' => [
                    'vehicle_type' => 'Luxury',
                ],
                'is_active' => true,
            ],

            [
                'id' => 3,
                'pricing_rule_type_id' => 2,
                'name' => 'Washing Service Extra Charge',
                'name_ar' => 'زيادة سعر خدمة الغسيل',
                'value' => 10,
                'conditions' => [
                    'service_type' => 'washing',
                ],
                'is_active' => true,
            ],

            [
                'id' => 4,
                'pricing_rule_type_id' => 2,
                'name' => 'Maintenance Service Extra Charge',
                'name_ar' => 'زيادة سعر خدمة الصيانة',
                'value' => 25,
                'conditions' => [
                    'service_type' => 'maintenance',
                ],
                'is_active' => true,
            ],

            [
                'id' => 5,
                'pricing_rule_type_id' => 3,
                'name' => 'Interior Cleaning Extra',
                'name_ar' => 'زيادة سعر تنظيف المقصورة',
                'value' => 15,
                'conditions' => [
                    'sub_service' => 'interior_cleaning',
                ],
                'is_active' => true,
            ],

            [
                'id' => 6,
                'pricing_rule_type_id' => 4,
                'name' => 'Company Customer Discount',
                'name_ar' => 'خصم عملاء الشركات',
                'value' => -10,
                'conditions' => [
                    'customer_type' => 'company',
                ],
                'is_active' => true,
            ],

            [
                'id' => 7,
                'pricing_rule_type_id' => 4,
                'name' => 'Personal Customer Price',
                'name_ar' => 'السعر الأساسي للعميل الشخصي',
                'value' => 0,
                'conditions' => [
                    'customer_type' => 'personal',
                ],
                'is_active' => true,
            ],

            [
                'id' => 8,
                'pricing_rule_type_id' => 5,
                'name' => 'Gold Package Discount',
                'name_ar' => 'خصم الباقة الذهبية',
                'value' => -15,
                'conditions' => [
                    'package' => 'gold',
                ],
                'is_active' => true,
            ],

            [
                'id' => 9,
                'pricing_rule_type_id' => 5,
                'name' => 'Silver Package Discount',
                'name_ar' => 'خصم الباقة الفضية',
                'value' => -5,
                'conditions' => [
                    'package' => 'silver',
                ],
                'is_active' => true,
            ],

            [
                'id' => 10,
                'pricing_rule_type_id' => 6,
                'name' => 'Premium Branch Extra Charge',
                'name_ar' => 'زيادة سعر الفرع المميز',
                'value' => 10,
                'conditions' => [
                    'branch_type' => 'premium',
                ],
                'is_active' => true,
            ],

            [
                'id' => 11,
                'pricing_rule_type_id' => 7,
                'name' => 'Weekend Extra Charge',
                'name_ar' => 'زيادة سعر عطلة نهاية الأسبوع',
                'value' => 10,
                'conditions' => [
                    'days' => [
                        'saturday',
                        'sunday',
                    ],
                ],
                'is_active' => true,
            ],

            [
                'id' => 12,
                'pricing_rule_type_id' => 8,
                'name' => 'Peak Hours Extra Charge',
                'name_ar' => 'زيادة سعر ساعات الذروة',
                'value' => 15,
                'conditions' => [
                    'start_time' => '17:00',
                    'end_time' => '21:00',
                ],
                'is_active' => true,
            ],

            [
                'id' => 13,
                'pricing_rule_type_id' => 9,
                'name' => 'Summer Extra Charge',
                'name_ar' => 'زيادة سعر موسم الصيف',
                'value' => 10,
                'conditions' => [
                    'season' => 'summer',
                ],
                'is_active' => true,
            ],

            [
                'id' => 14,
                'pricing_rule_type_id' => 10,
                'name' => 'Emergency Booking Extra',
                'name_ar' => 'زيادة سعر الحجز الطارئ',
                'value' => 20,
                'conditions' => [
                    'booking_type' => 'emergency',
                ],
                'is_active' => true,
            ],

            [
                'id' => 15,
                'pricing_rule_type_id' => 11,
                'name' => 'Extra Distance Charge',
                'name_ar' => 'رسوم المسافة الإضافية',
                'value' => 2, // price per km beyond the included distance
                'conditions' => [
                    'included_km' => 20,
                ],
                'is_active' => true,
            ],

            [
                'id' => 16,
                'pricing_rule_type_id' => 12,
                'name' => 'Off Working Hours Charge',
                'name_ar' => 'رسوم خارج أوقات العمل',
                'value' => 15,
                'conditions' => null,
                'is_active' => true,
            ],
        ];
        foreach ($rules as $rule) {
            PricingRule::create($rule);
        }    }
}
