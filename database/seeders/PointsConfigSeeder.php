<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PointsConfigSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('points_configs')->insert([
            [
                'earn_per_amount' => 10.00,
                'redeem_value' => 0.05,
                'min_redeem' => 100,
                'expiry_days' => 365,
                'max_earn_per_order' => 50,
                'is_active' => true,
                'updated_at' => now(),
            ],
            [
                'earn_per_amount' => 5.00,
                'redeem_value' => 0.10,
                'min_redeem' => 50,
                'expiry_days' => 365,
                'max_earn_per_order' => 100,
                'is_active' => false,
                'updated_at' => now(),
            ],
            [
                'earn_per_amount' => 3.00,
                'redeem_value' => 0.15,
                'min_redeem' => 30,
                'expiry_days' => 730,
                'max_earn_per_order' => 200,
                'is_active' => false,
                'updated_at' => now(),
            ],
        ]);
    }
}
