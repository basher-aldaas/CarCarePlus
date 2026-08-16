<?php
//
//
//namespace Database\Seeders;
//
//use App\Enums\OrderEnums\OrderStatus;
//use Illuminate\Database\Seeder;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Str;
//use RuntimeException;
//
//class AiOrderSeeder extends Seeder
//{
//    public function run(): void
//    {
//        /*
//        |--------------------------------------------------------------------------
//        | Customer
//        |--------------------------------------------------------------------------
//        */
//
//        $customer = DB::table('users')->first();
//
//        if (!$customer) {
//            throw new RuntimeException(
//                'No users found. Please seed users first.'
//            );
//        }
//
//        /*
//        |--------------------------------------------------------------------------
//        | Car
//        |--------------------------------------------------------------------------
//        */
//
//        $car = DB::table('cars')
//            ->where('user_id', $customer->id)
//            ->first();
//
//        if (!$car) {
//            $car = DB::table('cars')->first();
//        }
//
//        if (!$car) {
//            throw new RuntimeException(
//                'No cars found. Please seed cars first.'
//            );
//        }
//
//        /*
//        |--------------------------------------------------------------------------
//        | Service
//        |--------------------------------------------------------------------------
//        */
//
//        $service = DB::table('services')
//            ->where('name', 'Wheel Balancing')
//            ->first();
//
//        if (!$service) {
//            throw new RuntimeException(
//                'Wheel Balancing service not found. Run ServiceSeeder first.'
//            );
//        }
//
//        /*
//        |--------------------------------------------------------------------------
//        | Category
//        |--------------------------------------------------------------------------
//        */
//
//        $categoryId = $service->category_id;
//
//        /*
//        |--------------------------------------------------------------------------
//        | Branch
//        |--------------------------------------------------------------------------
//        */
//
//        $branch = DB::table('branches')->first();
//
//        /*
//        |--------------------------------------------------------------------------
//        | Employee
//        |--------------------------------------------------------------------------
//        */
//
//        $employee = DB::table('employees')->first();
//
//        /*
//        |--------------------------------------------------------------------------
//        | Create Order
//        |--------------------------------------------------------------------------
//        */
//
//        $orderId = DB::table('orders')->insertGetId([
//
//            'booking_group_id' => Str::uuid(),
//
//            'customer_id' => $customer->id,
//
//            'company_id' => null,
//
//            'car_id' => $car->id,
//
//            'branch_id' => $branch?->id,
//
//            'employee_id' => $employee?->id,
//
//            'category_id' => $categoryId,
//
//            'service_id' => $service->id,
//
//            'booking_type' => true,
//
//            'is_vip' => false,
//
//            'scheduled_at' => now(),
//
//            'started_at' => null,
//
//            'completed_at' => null,
//
//            'cancelled_at' => null,
//
//            'cancel_reason' => null,
//
//            'location_lat' => null,
//
//            'location_lng' => null,
//
//            'location_address' => null,
//
//            'distance_km' => null,
//
//            'discount_amount' => 0,
//
//            'total_price' => $service->base_price,
//
//            'notes' => 'AI diagnosis test order',
//
//            'status' => OrderStatus::values()[0],
//
//            'assigned_at' => null,
//
//            'created_at' => now(),
//
//            'updated_at' => now(),
//        ]);
//
//        $this->command->info(
//            "AI test order created successfully. Order ID: {$orderId}"
//        );
//    }
//}
