<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\User;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        // توزيع السيارات على العملاء (أفراد وشركات)
        $customers = User::role(['customer_personal', 'customer_company'])
            ->pluck('id');

        for ($i = 0; $i < 10; $i++) {
            Car::factory()->create([
                'user_id' => $customers->isNotEmpty()
                    ? $customers->random()
                    : User::factory(),
            ]);
        }
    }
}