<?php


namespace Database\Seeders;

use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarType;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AITestSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Customer
        |--------------------------------------------------------------------------
        */

        $customer = User::first();

        if (!$customer) {
            $this->command->error(
                'لا يوجد مستخدم في جدول users. أنشئ مستخدماً أولاً.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Car Type
        |--------------------------------------------------------------------------
        */

        $carType = CarType::first();

        if (!$carType) {
            $this->command->error(
                'لا يوجد CarType في جدول car_types.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Car Brand
        |--------------------------------------------------------------------------
        */

        $brand = CarBrand::first();

        if (!$brand) {
            $this->command->error(
                'لا يوجد CarBrand في جدول car_brands.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Branch
        |--------------------------------------------------------------------------
        */

        $branchId = DB::table('branches')->value('id');

        /*
        |--------------------------------------------------------------------------
        | 5. إنشاء السيارة
        |--------------------------------------------------------------------------
        */

        $car = Car::firstOrCreate(
            [
                'user_id' => $customer->id,
                'brand_id' => $brand->id,
                'car_type_id' => $carType->id,
                'plate_number' => 'AI-TEST-001',
            ],
            [
                'branch_id' => $branchId,
                'model' => 'Test Model',
                'year' => 2022,
                'color' => 'Black',
                'fuel_type' => 'petrol',
                'cylinders' => 4,
                'mileage' => 50000,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 6. الحصول على Wheel Balancing
        |--------------------------------------------------------------------------
        */

        $service = DB::table('services')
            ->where('name', 'Wheel Balancing')
            ->first();

        if (!$service) {
            $this->command->error(
                'خدمة Wheel Balancing غير موجودة. شغّل ServiceSeeder أولاً.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 7. إنشاء Order
        |--------------------------------------------------------------------------
        */

        $order = Order::create([
            'customer_id' => $customer->id,

            'company_id' => null,

            'car_id' => $car->id,

            'branch_id' => $branchId,

            'employee_id' => null,

            /*
            |--------------------------------------------------------------------------
            | نترك الخدمة فارغة في البداية
            |--------------------------------------------------------------------------
            |
            | لأن هدف الاختبار هو:
            |
            | AI Diagnosis
            |       ↓
            | recommended_service
            |       ↓
            | Apply Service
            |       ↓
            | service_id = Wheel Balancing
            |
            */

            'service_id' => null,

            'category_id' => null,

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

            /*
            |--------------------------------------------------------------------------
            | مهم
            |--------------------------------------------------------------------------
            |
            | غيّر هذه القيمة إذا كان OrderStatus عندك يستخدم قيمة مختلفة.
            |
            */

            'status' => 'pending',

            'assigned_at' => null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 8. عرض النتيجة
        |--------------------------------------------------------------------------
        */

        $this->command->info(
            'تم إنشاء بيانات الاختبار بنجاح.'
        );

        $this->command->line(
            'Customer ID: ' . $customer->id
        );

        $this->command->line(
            'Car ID: ' . $car->id
        );

        $this->command->line(
            'Service ID: ' . $service->id
        );

        $this->command->line(
            'ORDER ID: ' . $order->id
        );

        $this->command->newLine();

        $this->command->info(
            'استخدم Order ID أعلاه في Postman.'
        );
    }
}
