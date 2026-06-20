<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $washerUser = DB::table('users')->where('email', 'washer@system.com')->first();
        $mechanicUser = DB::table('users')->where('email', 'mechanic@system.com')->first();

        if ($washerUser) {
            DB::table('employees')->insertOrIgnore([
                'user_id' => $washerUser->id,
                'branch_id' => 1, // الفرع الرئيسي
                'type' => '0',      // 0 لـ washer حسب الـ ERD
                'is_active' => true,
                'rating_avg' => 5.0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($mechanicUser) {
            DB::table('employees')->insertOrIgnore([
                'user_id' => $mechanicUser->id,
                'branch_id' => 1,
                'type' => 1,      // 1 لـ technician/mechanic
                'is_active' => true,
                'rating_avg' => 4.9,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
