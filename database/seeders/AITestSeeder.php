<?php

namespace Database\Seeders;

use App\Enums\OrderEnums\OrderStatus;
use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarType;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AITestSeeder extends Seeder
{
    public function run(): void
    {

        $customer = User::first();

        if (!$customer) {
            $this->command->error(
                'لا يوجد مستخدم في جدول users. أنشئ مستخدماً أولاً.'
            );

            return;
        }


        $carType = CarType::first();

        if (!$carType) {
            $this->command->error(
                'لا يوجد CarType في جدول car_types.'
            );

            return;
        }


        $brand = CarBrand::first();

        if (!$brand) {
            $this->command->error(
                'لا يوجد CarBrand في جدول car_brands.'
            );

            return;
        }

        $branchId = DB::table('branches')->value('id');


        $car = Car::firstOrCreate(
            [
                'user_id' => $customer->id,
                'brand_id' => $brand->id,
                'car_type_id' => $carType->id,
                'plate_number' => 'AI-TEST-001',
            ],
            [
                'model' => 'Test Model',
                'year' => 2022,
                'color' => 'Black',
                'fuel_type' => 'petrol',
                'cylinders' => 4,
                'mileage' => 50000,
                'is_active' => true,
            ]
        );



        $service = DB::table('services')
            ->where('name', 'Wheel Balancing')
            ->first();

        if (!$service) {
            $this->command->error(
                'خدمة Wheel Balancing غير موجودة. تأكد من تشغيل ServiceSeeder.'
            );

            return;
        }


        $order = Order::create([
            'booking_group_id' => (string) Str::uuid(),

            'customer_id' => $customer->id,

            'company_id' => null,

            'car_id' => $car->id,


            'branch_id' => $branchId,

            'employee_id' => null,

            'service_id' => $service->id,

            'category_id' => $service->category_id,

            'booking_type' => false,

            'is_vip' => false,

            'scheduled_at' => now(),

            'started_at' => null,

            'completed_at' => null,

            'cancelled_at' => null,

            'cancel_reason' => null,

            'location_lat' => null,

            'location_lng' => null,

            'location_address' => null,

            'distance_km' => null,

            'discount_amount' => 0,

            'total_price' => 0,

            'notes' => 'AI Diagnosis Test Order',

            'status' => OrderStatus::values()[0],

            'assigned_at' => null,
        ]);


        $this->command->newLine();

        $this->command->info(
            'تم إنشاء بيانات اختبار AI بنجاح.'
        );

        $this->command->line(
            'Customer ID: ' . $customer->id
        );

        $this->command->line(
            'Car ID: ' . $car->id
        );

        $this->command->line(
            'Brand ID: ' . $brand->id
        );

        $this->command->line(
            'Car Type ID: ' . $carType->id
        );

        $this->command->line(
            'Service ID: ' . $service->id
        );

        $this->command->line(
            'ORDER ID: ' . $order->id
        );

        $this->command->line(
            'Branch ID: ' . ($branchId ?? 'NULL')
        );

        $this->command->newLine();

        $this->command->info(
            'استخدم ORDER ID أعلاه في Postman لاختبار AI Diagnosis.'
        );
    }
}
