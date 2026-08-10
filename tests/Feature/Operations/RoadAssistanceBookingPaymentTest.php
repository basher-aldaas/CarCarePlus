<?php

namespace Tests\Feature\Operations;

use App\Enums\PackageType;
use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Models\Branch;
use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarType;
use App\Models\Category;
use App\Models\Order;
use App\Models\Package;
use App\Models\PackageService;
use App\Models\ProblemType;
use App\Models\Service;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\Wallet;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoadAssistanceBookingPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function customer(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('customer_personal');
        Sanctum::actingAs($user);

        return $user;
    }

    private function carFor(User $customer, string $carTypeName = 'SUV'): Car
    {
        $brand = CarBrand::create(['name' => 'Brand ' . uniqid(), 'is_active' => true]);
        $carType = CarType::create(['name' => $carTypeName, 'name_ar' => 'نوع', 'price_multiplier' => 1.2, 'is_active' => true]);

        return Car::create([
            'user_id' => $customer->id,
            'brand_id' => $brand->id,
            'car_type_id' => $carType->id,
            'plate_number' => 'ABC-' . uniqid(),
            'model' => 'Camry',
            'year' => 2022,
            'color' => 'White',
            'fuel_type' => 'petrol',
            'cylinders' => 4,
            'mileage' => 10000,
            'is_active' => true,
        ]);
    }

    private function roadAssistanceService(float $basePrice = 100): Service
    {
        $category = Category::create(['name' => 'Roadside Assistance', 'name_ar' => 'المساعدة على الطريق', 'is_active' => true]);

        return Service::create([
            'category_id' => $category->id,
            'name' => 'Battery Jump Start',
            'name_ar' => 'تشغيل البطارية',
            'description' => 'desc',
            'base_price' => $basePrice,
            'is_vip_available' => false,
            'duration_minutes' => 30,
        ]);
    }

    private function problemType(): ProblemType
    {
        return ProblemType::create([
            'name' => 'Dead Battery',
            'name_ar' => 'بطارية فارغة',
            'is_active' => true,
        ]);
    }

    private function branch(): Branch
    {
        $admin = User::factory()->create(['is_active' => true]);

        return Branch::create([
            'admin_id' => $admin->id,
            'name' => 'Branch ' . uniqid(),
            'name_ar' => 'فرع',
            'city' => 'City',
            'address' => 'Address',
            'phone' => '0000000000',
            'is_active' => true,
        ]);
    }

    private function packageCovering(Service $service): Package
    {
        $package = Package::create([
            'name' => 'Package ' . uniqid(),
            'description' => 'desc',
            'type' => PackageType::MONTHLY->value,
            'is_company_package' => false,
            'price' => 200,
            'discount_pct' => 0,
            'services_count' => 1,
            'valid_days' => 30,
            'is_active' => true,
        ]);

        PackageService::create([
            'package_id' => $package->id,
            'service_id' => $service->id,
            'allowed_count' => 1,
        ]);

        return $package;
    }

    private function userPackageFor(User $customer, Package $package, int $remainingCount = 5): UserPackage
    {
        return UserPackage::create([
            'user_id' => $customer->id,
            'package_id' => $package->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(30),
            'remaining_count' => $remainingCount,
            'status' => \App\Enums\UserPackageStatus::ACTIVE->value,
        ]);
    }

    private function basePayload(Car $car, Service $service, ProblemType $problemType, string $paymentMethod, ?Branch $branch = null): array
    {
        return [
            'car_ids' => [$car->id],
            'service_id' => $service->id,
            'branch_id' => $branch?->id,
            'booking_type' => true,
            'payment_method' => $paymentMethod,
            'problem_type_id' => $problemType->id,
            'problem_description' => 'Car will not start, battery seems dead.',
        ];
    }

    public function test_road_assistance_booking_with_cash_payment_succeeds(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer, 'Truck / Pickup');
        $service = $this->roadAssistanceService(100);
        $problemType = $this->problemType();

        $quote = $this->postJson('/api/bookings/quote', $this->basePayload($car, $service, $problemType, PaymentMethod::CASH->value))
            ->assertOk()
            ->json('data');

        $this->assertNotEmpty($quote['quote_token']);

        $res = $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']]);
        $res->assertOk();

        $order = Order::firstOrFail();
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => PaymentMethod::CASH->value,
            'status' => PaymentStatus::PENDING->value,
        ]);

        // car_type_size must be derived from the car's CarType (pickup), never client-supplied
        $this->assertDatabaseHas('road_assistance_details', [
            'order_id' => $order->id,
            'problem_type_id' => $problemType->id,
            'car_type_size' => 'pickup',
        ]);
    }

    public function test_road_assistance_booking_with_wallet_payment_succeeds(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer, 'SUV');
        $service = $this->roadAssistanceService(100);
        $problemType = $this->problemType();

        Wallet::create(['user_id' => $customer->id, 'balance' => 500]);

        $quote = $this->postJson('/api/bookings/quote', $this->basePayload($car, $service, $problemType, PaymentMethod::WALLET->value))
            ->assertOk()
            ->json('data');

        $res = $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']]);
        $res->assertOk();

        $order = Order::firstOrFail();
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => PaymentMethod::WALLET->value,
            'status' => PaymentStatus::PAID->value,
        ]);

        $this->assertDatabaseHas('road_assistance_details', [
            'order_id' => $order->id,
            'car_type_size' => 'suv',
        ]);

        $this->assertEquals(500 - (float) $order->total_price, (float) Wallet::where('user_id', $customer->id)->value('balance'));
    }

    public function test_road_assistance_booking_with_insufficient_wallet_balance_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->roadAssistanceService(100);
        $problemType = $this->problemType();

        Wallet::create(['user_id' => $customer->id, 'balance' => 10]);

        $res = $this->postJson('/api/bookings/quote', $this->basePayload($car, $service, $problemType, PaymentMethod::WALLET->value));

        $res->assertStatus(422)->assertJsonValidationErrors('wallet', 'data');
    }

    public function test_road_assistance_booking_with_package_payment_succeeds(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer, 'Sedan');
        $service = $this->roadAssistanceService(100);
        $problemType = $this->problemType();
        $package = $this->packageCovering($service);
        $userPackage = $this->userPackageFor($customer, $package);

        $quote = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service, $problemType, PaymentMethod::PACKAGE->value),
            'user_package_id' => $userPackage->id,
        ])->assertOk()->json('data');

        $this->assertNotEmpty($quote['quote_token']);

        $res = $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']]);
        $res->assertOk();

        $this->assertEquals(4, $userPackage->fresh()->remaining_count);

        $order = Order::firstOrFail();
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => PaymentMethod::PACKAGE->value,
            'status' => PaymentStatus::PAID->value,
            'package_id' => $package->id,
        ]);

        $this->assertDatabaseHas('road_assistance_details', [
            'order_id' => $order->id,
            'car_type_size' => 'sedan',
        ]);
    }

    public function test_service_outside_road_assistance_category_is_rejected_when_problem_type_sent(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $problemType = $this->problemType();

        $category = Category::create(['name' => 'Car Wash', 'name_ar' => 'غسيل', 'is_active' => true]);
        $washService = Service::create([
            'category_id' => $category->id,
            'name' => 'Basic Wash',
            'name_ar' => 'غسيل أساسي',
            'description' => 'desc',
            'base_price' => 50,
            'is_vip_available' => false,
            'duration_minutes' => 30,
        ]);

        $res = $this->postJson('/api/bookings/quote', $this->basePayload($car, $washService, $problemType, PaymentMethod::CASH->value));

        $res->assertStatus(422)->assertJsonValidationErrors('service_id', 'data');
    }
}