<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $hasCustomers = User::role(['customer_personal', 'customer_company'])->exists();

        if (! $hasCustomers || ! Car::query()->exists() || ! Service::query()->exists()) {
            return;
        }

        Order::factory()->count(40)->create();
    }
}