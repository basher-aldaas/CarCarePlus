<?php

namespace Database\Seeders;

use App\Enums\EmployeeType;
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
                'type' => EmployeeType::WASHER->value,
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
                'type' => EmployeeType::MECHANIC->value,
                'is_active' => true,
                'rating_avg' => 4.9,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
