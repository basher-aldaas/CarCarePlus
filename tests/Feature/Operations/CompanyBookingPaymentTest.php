<?php

namespace Tests\Feature\Operations;

use App\Enums\PackageType;
use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Enums\UserPackageStatus;
use App\Models\Branch;
use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarType;
use App\Models\Category;
use App\Models\Company;
use App\Models\Order;
use App\Models\Package;
use App\Models\PackageService;
use App\Models\ProblemType;
use App\Models\Service;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\Wallet;
use App\Models\Workshop;
use Database\Seeders\PricingRuleSeeder;
use Database\Seeders\PricingRuleTypeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Mirrors the personal-customer booking tests, but for a customer_company
 * account. Focuses on what is different for a company: it may book several
 * cars in one request (each order shares a booking_group_id and carries the
 * company_id), its bookings are gated on the company being active, and it
 * earns the "customer_type = company" pricing discount.
 */
class CompanyBookingPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * A company customer: a user with the customer_company role that owns an
     * active Company row (resolveCompany() matches on Company.customer_id).
     */
    private function companyCustomer(bool $companyActive = true): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('customer_company');

        Company::create([
            'customer_id' => $user->id,
            'name' => 'Company ' . uniqid(),
            'name_ar' => 'شركة',
            'commercial_reg' => (string) random_int(1000000000, 9999999999),
            'tax_number' => '15' . random_int(1000000000, 9999999999),
            'address' => 'Address',
            'is_active' => $companyActive,
        ]);

        Sanctum::actingAs($user);

        return $user;
    }

    private function carFor(User $customer, string $carTypeName = 'Sedan', float $multiplier = 1.0): Car
    {
        $brand = CarBrand::create(['name' => 'Brand ' . uniqid(), 'is_active' => true]);
        $carType = CarType::create(['name' => $carTypeName, 'name_ar' => 'نوع', 'price_multiplier' => $multiplier, 'is_active' => true]);

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

    private function service(float $basePrice = 100, string $categoryName = 'Car Wash', string $serviceName = 'Basic Wash'): Service
    {
        $category = Category::create(['name' => $categoryName, 'name_ar' => 'فئة', 'is_active' => true]);

        return Service::create([
            'category_id' => $category->id,
            'name' => $serviceName,
            'name_ar' => 'خدمة',
            'description' => 'desc',
            'base_price' => $basePrice,
            'is_vip_available' => false,
            'duration_minutes' => 30,
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

    private function workshop(): Workshop
    {
        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('workshop');

        return Workshop::create([
            'user_id' => $owner->id,
            'name' => 'Workshop ' . uniqid(),
            'name_ar' => 'ورشة',
            'address' => 'Industrial Area',
            'city' => 'Riyadh',
            'latitude' => 24.6330,
            'longitude' => 46.7167,
            'status' => 'active',
            'rating_avg' => 4.5,
        ]);
    }

    private function packageCovering(Service $service, int $allowedCount = 1): Package
    {
        $package = Package::create([
            'name' => 'Package ' . uniqid(),
            'description' => 'desc',
            'type' => PackageType::MONTHLY->value,
            'is_company_package' => true,
            'price' => 200,
            'discount_pct' => 0,
            'services_count' => 1,
            'valid_days' => 30,
            'is_active' => true,
        ]);

        PackageService::create([
            'package_id' => $package->id,
            'service_id' => $service->id,
            'allowed_count' => $allowedCount,
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
            'status' => UserPackageStatus::ACTIVE->value,
        ]);
    }

    private function basePayload(array $carIds, Service $service, string $paymentMethod, ?Branch $branch = null): array
    {
        return [
            'car_ids' => $carIds,
            'service_id' => $service->id,
            'branch_id' => $branch?->id,
            'booking_type' => false,
            // a fixed weekday so the seeded "Weekend Extra Charge" rule never applies
            'scheduled_at' => now()->next(\Carbon\Carbon::MONDAY)->setTime(10, 0)->toDateTimeString(),
            'payment_method' => $paymentMethod,
        ];
    }

    // ---- Multi-car grouping (the defining company behaviour) ---------------

    public function test_company_can_book_multiple_cars_in_one_group_with_cash(): void
    {
        $customer = $this->companyCustomer();
        $carA = $this->carFor($customer);
        $carB = $this->carFor($customer);
        $service = $this->service(100);

        $quote = $this->postJson('/api/bookings/quote', $this->basePayload([$carA->id, $carB->id], $service, PaymentMethod::CASH->value))
            ->assertOk()
            ->json('data');

        $this->assertEquals(2, $quote['car_count']);
        $this->assertEquals(200.0, (float) $quote['total_price']);

        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        $orders = Order::all();
        $this->assertCount(2, $orders);

        // both orders share one booking_group_id and carry the company_id
        $companyId = Company::where('customer_id', $customer->id)->value('id');
        $groupIds = $orders->pluck('booking_group_id')->unique();

        $this->assertCount(1, $groupIds);
        $this->assertNotNull($groupIds->first());
        $this->assertTrue($orders->every(fn (Order $o) => $o->company_id === $companyId));

        foreach ($orders as $order) {
            $this->assertDatabaseHas('payments', [
                'order_id' => $order->id,
                'method' => PaymentMethod::CASH->value,
                'status' => PaymentStatus::PENDING->value,
            ]);
        }
    }

    public function test_single_car_company_booking_still_gets_group_id_and_company_id(): void
    {
        $customer = $this->companyCustomer();
        $car = $this->carFor($customer);
        $service = $this->service(100);

        $quote = $this->postJson('/api/bookings/quote', $this->basePayload([$car->id], $service, PaymentMethod::CASH->value))
            ->assertOk()
            ->json('data');

        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        $order = Order::firstOrFail();
        $companyId = Company::where('customer_id', $customer->id)->value('id');

        $this->assertEquals($companyId, $order->company_id);
        $this->assertNotNull($order->booking_group_id);
    }

    // ---- Payment methods ---------------------------------------------------

    public function test_company_multi_car_wallet_payment_debits_full_group(): void
    {
        $customer = $this->companyCustomer();
        Wallet::create(['user_id' => $customer->id, 'balance' => 1000]);
        $carA = $this->carFor($customer);
        $carB = $this->carFor($customer);
        $service = $this->service(100);

        $quote = $this->postJson('/api/bookings/quote', $this->basePayload([$carA->id, $carB->id], $service, PaymentMethod::WALLET->value))
            ->assertOk()
            ->json('data');

        $this->assertEquals(200.0, (float) $quote['total_price']);

        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        // both cars debited: 1000 - 100 - 100 = 800
        $this->assertEquals(800.0, (float) Wallet::where('user_id', $customer->id)->value('balance'));

        foreach (Order::all() as $order) {
            $this->assertDatabaseHas('payments', [
                'order_id' => $order->id,
                'method' => PaymentMethod::WALLET->value,
                'status' => PaymentStatus::PAID->value,
            ]);
        }
    }

    public function test_company_wallet_balance_checked_against_full_group_total(): void
    {
        $customer = $this->companyCustomer();
        // enough for one car (100) but not for two (200)
        Wallet::create(['user_id' => $customer->id, 'balance' => 150]);
        $carA = $this->carFor($customer);
        $carB = $this->carFor($customer);
        $service = $this->service(100);

        $res = $this->postJson('/api/bookings/quote', $this->basePayload([$carA->id, $carB->id], $service, PaymentMethod::WALLET->value));

        $res->assertStatus(422)->assertJsonValidationErrors('wallet', 'data');
    }

    public function test_company_multi_car_package_payment_deducts_one_use_per_car(): void
    {
        $customer = $this->companyCustomer();
        $carA = $this->carFor($customer);
        $carB = $this->carFor($customer);
        $service = $this->service(100);
        $package = $this->packageCovering($service, allowedCount: 5);
        $userPackage = $this->userPackageFor($customer, $package, remainingCount: 5);

        $quote = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload([$carA->id, $carB->id], $service, PaymentMethod::PACKAGE->value),
            'user_package_id' => $userPackage->id,
        ])->assertOk()->json('data');

        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        // one use consumed per car
        $this->assertEquals(3, $userPackage->fresh()->remaining_count);

        foreach (Order::all() as $order) {
            $this->assertDatabaseHas('payments', [
                'order_id' => $order->id,
                'method' => PaymentMethod::PACKAGE->value,
                'status' => PaymentStatus::PAID->value,
                'package_id' => $package->id,
            ]);
        }
    }

    public function test_company_package_without_enough_remaining_uses_for_all_cars_is_rejected(): void
    {
        $customer = $this->companyCustomer();
        $carA = $this->carFor($customer);
        $carB = $this->carFor($customer);
        $service = $this->service(100);
        $package = $this->packageCovering($service, allowedCount: 5);
        // only one use left but two cars requested
        $userPackage = $this->userPackageFor($customer, $package, remainingCount: 1);

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload([$carA->id, $carB->id], $service, PaymentMethod::PACKAGE->value),
            'user_package_id' => $userPackage->id,
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('user_package_id', 'data');
    }

    // ---- Service types -----------------------------------------------------

    public function test_company_road_assistance_booking_multi_car_with_cash(): void
    {
        $customer = $this->companyCustomer();
        $carA = $this->carFor($customer, 'SUV', 1.2);
        $carB = $this->carFor($customer, 'Truck / Pickup', 1.2);
        $service = $this->service(100, 'Roadside Assistance', 'Battery Jump Start');
        $problemType = ProblemType::create(['name' => 'Dead Battery', 'name_ar' => 'بطارية', 'is_active' => true]);

        $quote = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload([$carA->id, $carB->id], $service, PaymentMethod::CASH->value),
            'booking_type' => true,
            'scheduled_at' => null,
            'problem_type_id' => $problemType->id,
            'problem_description' => 'Battery seems dead on both cars.',
        ])->assertOk()->json('data');

        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        $this->assertCount(2, Order::all());

        // car_type_size derived per car from its own CarType, never client supplied
        $this->assertDatabaseHas('road_assistance_details', [
            'order_id' => Order::where('car_id', $carA->id)->value('id'),
            'car_type_size' => 'suv',
        ]);
        $this->assertDatabaseHas('road_assistance_details', [
            'order_id' => Order::where('car_id', $carB->id)->value('id'),
            'car_type_size' => 'pickup',
        ]);
    }

    public function test_company_maintenance_booking_records_workshop_per_car(): void
    {
        $customer = $this->companyCustomer();
        $carA = $this->carFor($customer);
        $carB = $this->carFor($customer);
        $service = $this->service(100, 'Maintenance', 'General Inspection');
        $workshop = $this->workshop();

        $quote = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload([$carA->id, $carB->id], $service, PaymentMethod::CASH->value),
            'booking_type' => true,
            'scheduled_at' => null,
            'workshop_id' => $workshop->id,
        ])->assertOk()->json('data');

        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        $this->assertCount(2, Order::all());
        foreach (Order::all() as $order) {
            $this->assertDatabaseHas('maintenance_details', [
                'order_id' => $order->id,
                'workshop_id' => $workshop->id,
            ]);
        }
    }

    public function test_company_towing_booking_records_destination_and_car_size(): void
    {
        $customer = $this->companyCustomer();
        $car = $this->carFor($customer, 'Truck / Pickup', 1.2);
        $service = $this->service(150, 'Flatbed Towing', 'Flatbed Tow');

        $quote = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload([$car->id], $service, PaymentMethod::CASH->value),
            'booking_type' => true,
            'scheduled_at' => null,
            'destination_lat' => 24.7,
            'destination_lng' => 46.7,
            'destination_address' => 'Central Garage, Riyadh',
        ])->assertOk()->json('data');

        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        $order = Order::firstOrFail();
        $this->assertDatabaseHas('towing_details', [
            'order_id' => $order->id,
            'car_type_size' => 'pickup',
        ]);
    }

    // ---- Company-specific gating & pricing ---------------------------------

    public function test_inactive_company_booking_is_rejected(): void
    {
        $customer = $this->companyCustomer(companyActive: false);
        $car = $this->carFor($customer);
        $service = $this->service(100);

        $res = $this->postJson('/api/bookings/quote', $this->basePayload([$car->id], $service, PaymentMethod::CASH->value));

        // BookingCompanyInactiveException renders as 403 Forbidden
        $res->assertStatus(403);
    }

    public function test_company_cannot_book_a_car_it_does_not_own(): void
    {
        $customer = $this->companyCustomer();
        $ownCar = $this->carFor($customer);

        $stranger = User::factory()->create(['is_active' => true]);
        $foreignCar = $this->carFor($stranger);

        $service = $this->service(100);

        $res = $this->postJson('/api/bookings/quote', $this->basePayload([$ownCar->id, $foreignCar->id], $service, PaymentMethod::CASH->value));

        $res->assertStatus(422)->assertJsonValidationErrors('car_ids', 'data');
    }

    public function test_company_customer_receives_the_company_discount(): void
    {
        // customer_type = company rule (-10) only exists once pricing rules are seeded
        $this->seed(PricingRuleTypeSeeder::class);
        $this->seed(PricingRuleSeeder::class);

        $customer = $this->companyCustomer();
        $car = $this->carFor($customer);
        $service = $this->service(100);

        $quote = $this->postJson('/api/bookings/quote', $this->basePayload([$car->id], $service, PaymentMethod::CASH->value))
            ->assertOk()
            ->json('data');

        // base 100 minus the 10 company discount
        $this->assertEquals(90.0, (float) $quote['invoice'][0]['service_price']);
        $this->assertEquals(90.0, (float) $quote['total_price']);

        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        $order = Order::firstOrFail();
        $this->assertEquals(90.0, (float) $order->service_price);
        $this->assertEquals(10.0, (float) $order->discount_amount);
    }
}