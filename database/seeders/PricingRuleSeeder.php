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
                'conditions' => [
                    'start_time' => '18:00',
                    'end_time' => '08:00',
                ],
                'is_active' => true,
            ],
        ];
        foreach ($rules as $rule) {
            PricingRule::create($rule);
        }    }
}
