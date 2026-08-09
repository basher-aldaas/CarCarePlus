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
                'id' => 2,
                'pricing_rule_type_id' => 6,
                'name' => 'Premium Branch Extra Charge',
                'name_ar' => 'زيادة سعر الفرع المميز',
                'value' => 15,
                'conditions' => [
                    'branch_type' => 'premium',
                ],
                'is_active' => true,
            ],

            [
                'id' => 3,
                'pricing_rule_type_id' => 7,
                'name' => 'Weekend Extra Charge',
                'name_ar' => 'زيادة سعر عطلة نهاية الأسبوع',
                'value' => 15,
                'conditions' => [
                    'days' => [
                        'saturday',
                        'sunday',
                    ],
                ],
                'is_active' => true,
            ],


            [
                'id' => 4,
                'pricing_rule_type_id' => 10,
                'name' => 'Emergency Booking Extra',
                'name_ar' => 'زيادة سعر الحجز الطارئ',
                'value' => 25,
                'conditions' => [
                    'booking_type' => 'emergency',
                ],
                'is_active' => true,
            ],

            [
                'id' => 5,
                'pricing_rule_type_id' => 11,
                'name' => 'Extra Distance Charge',
                'name_ar' => 'رسوم المسافة الإضافية',
                'value' => 0.5, // price per km beyond the included distance
                'conditions' => [
                    'included_km' => 20,
                ],
                'is_active' => true,
            ],
            [
                'id' => 6,
                'pricing_rule_type_id' => 10,
                'name' => 'Immediate Booking Charge',
                'name_ar' => 'حجز فوري',
                'value' => 15,
                'conditions' => [],
                'is_active' => true,
            ],

        ];
        foreach ($rules as $rule) {
            PricingRule::create($rule);
        }    }
}
