<?php

namespace Tests\Feature\Operations;

use App\Enums\PackageType;
use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\UserPackageStatus;
use App\Models\Branch;
use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarType;
use App\Models\Category;
use App\Models\Order;
use App\Models\Package;
use App\Models\PackageService;
use App\Models\Service;
use App\Models\SubService;
use App\Models\User;
use App\Models\UserPackage;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Covers BookingService::updateBooking() — the customer-editable fields
 * (scheduled_at, booking_type, is_vip, sub_service_ids), the reprice they
 * trigger, the booking_type/scheduled_at consistency rules, and the
 * package-booking restriction.
 *
 * The update endpoint is gated by `edit.order`, which customers don't hold, so
 * updates are driven here as a super admin (who passes both the gate and
 * canManage()); one test documents the customer 403.
 */
class BookingUpdateTest extends TestCase
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

    private function actingAsSuperAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('super_admin');
        Sanctum::actingAs($user);

        return $user;
    }

    private function carFor(User $customer): Car
    {
        $brand = CarBrand::create(['name' => 'Brand ' . uniqid(), 'is_active' => true]);
        $carType = CarType::create(['name' => 'Sedan', 'name_ar' => 'سيدان', 'price_multiplier' => 1, 'is_active' => true]);

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

    private function service(float $basePrice = 100, float $vipExtra = 0): Service
    {
        $category = Category::create(['name' => 'Category ' . uniqid(), 'name_ar' => 'فئة', 'is_active' => true]);

        return Service::create([
            'category_id' => $category->id,
            'name' => 'Basic Wash',
            'name_ar' => 'غسيل أساسي',
            'description' => 'desc',
            'base_price' => $basePrice,
            'is_vip_available' => $vipExtra > 0,
            'vip_extra_price' => $vipExtra,
            'duration_minutes' => 30,
        ]);
    }

    private function subServiceFor(Service $service, float $price): SubService
    {
        return SubService::create([
            'service_id' => $service->id,
            'name' => 'Sub ' . uniqid(),
            'name_ar' => 'فرعي',
            'price' => $price,
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

    /** A package covering $service (fully), with an active user package for $customer. */
    private function packageFor(User $customer, Service $service): UserPackage
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

        return UserPackage::create([
            'user_id' => $customer->id,
            'package_id' => $package->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(30),
            'remaining_count' => 5,
            'status' => UserPackageStatus::ACTIVE->value,
        ]);
    }

    private function scheduledPayload(Car $car, Service $service, Branch $branch): array
    {
        return [
            'car_ids' => [$car->id],
            'service_id' => $service->id,
            'branch_id' => $branch->id,
            'location_lat' => 24.7136,
            'location_lng' => 46.6753,
            'booking_type' => false,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'payment_method' => PaymentMethod::CASH->value,
        ];
    }

    /** Run the real quote + confirm flow (as the current customer) and return the created order. */
    private function confirmBooking(array $payload): Order
    {
        $quote = $this->postJson('/api/bookings/quote', $payload)->json('data');
        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        return Order::latest('id')->firstOrFail();
    }

    private function updateBooking(int $orderId, array $body)
    {
        return $this->postJson("/api/bookings/{$orderId}", $body);
    }

    public function test_update_is_vip_reprices_service_and_total(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100, vipExtra: 50);
        $branch = $this->branch();

        $order = $this->confirmBooking($this->scheduledPayload($car, $service, $branch));
        $this->assertEquals(100.0, (float) $order->service_price);

        $this->actingAsSuperAdmin();
        $this->updateBooking($order->id, ['is_vip' => true])->assertOk();

        $order->refresh();
        $this->assertTrue((bool) $order->is_vip);
        $this->assertEquals(150.0, (float) $order->service_price);
        $this->assertEquals(150.0, (float) $order->total_price);

        $this->assertDatabaseHas('order_price_items', [
            'order_id' => $order->id,
            'label' => 'VIP Service Charge',
            'amount' => 50.00,
        ]);
    }

    public function test_update_to_immediate_clears_stored_scheduled_at(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $branch = $this->branch();

        $order = $this->confirmBooking($this->scheduledPayload($car, $service, $branch));
        $this->assertNotNull($order->scheduled_at);

        $this->actingAsSuperAdmin();
        $this->updateBooking($order->id, ['booking_type' => true])->assertOk();

        $order->refresh();
        $this->assertTrue((bool) $order->booking_type);
        $this->assertNull($order->scheduled_at);
    }

    public function test_update_rejects_scheduled_at_when_switching_to_immediate(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $branch = $this->branch();

        $order = $this->confirmBooking($this->scheduledPayload($car, $service, $branch));

        $this->actingAsSuperAdmin();
        $this->updateBooking($order->id, [
            'booking_type' => true,
            'scheduled_at' => now()->addDays(2)->toDateTimeString(),
        ])->assertStatus(422)->assertJsonValidationErrors('scheduled_at', 'data');

        $order->refresh();
        $this->assertFalse((bool) $order->booking_type);
        $this->assertNotNull($order->scheduled_at);
    }

    public function test_update_to_scheduled_without_a_time_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $branch = $this->branch();

        $order = $this->confirmBooking($this->scheduledPayload($car, $service, $branch));

        // Switch to immediate first, which clears the stored scheduled_at...
        $this->actingAsSuperAdmin();
        $this->updateBooking($order->id, ['booking_type' => true])->assertOk();
        $this->assertNull($order->refresh()->scheduled_at);

        // ...so switching back to scheduled with no time now has nothing to fall back on.
        $this->updateBooking($order->id, ['booking_type' => false])
            ->assertStatus(422)->assertJsonValidationErrors('scheduled_at', 'data');
    }

    public function test_update_to_scheduled_with_a_time_sets_it(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $branch = $this->branch();

        $order = $this->confirmBooking($this->scheduledPayload($car, $service, $branch));

        $this->actingAsSuperAdmin();
        $this->updateBooking($order->id, ['booking_type' => true])->assertOk();

        $when = now()->addDays(3)->startOfMinute();
        $this->updateBooking($order->id, [
            'booking_type' => false,
            'scheduled_at' => $when->toDateTimeString(),
        ])->assertOk();

        $order->refresh();
        $this->assertFalse((bool) $order->booking_type);
        $this->assertEquals($when->toDateTimeString(), $order->scheduled_at->toDateTimeString());
    }

    public function test_update_replaces_sub_services_and_reprices_total(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);
        $subA = $this->subServiceFor($service, 20);
        $subB = $this->subServiceFor($service, 35);
        $branch = $this->branch();

        $order = $this->confirmBooking([
            ...$this->scheduledPayload($car, $service, $branch),
            'sub_service_ids' => [$subA->id],
        ]);
        $this->assertEquals(20.0, (float) $order->sub_service_price);
        $this->assertEquals(120.0, (float) $order->total_price);

        $this->actingAsSuperAdmin();
        $this->updateBooking($order->id, ['sub_service_ids' => [$subB->id]])->assertOk();

        $order->refresh();
        $this->assertEquals(35.0, (float) $order->sub_service_price);
        $this->assertEquals(135.0, (float) $order->total_price);
        $this->assertEquals(1, $order->subServices()->count());
        $this->assertDatabaseHas('order_sub_services', ['order_id' => $order->id, 'sub_service_id' => $subB->id]);
        $this->assertDatabaseMissing('order_sub_services', ['order_id' => $order->id, 'sub_service_id' => $subA->id]);
    }

    public function test_update_with_empty_sub_services_clears_them(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);
        $subA = $this->subServiceFor($service, 20);
        $branch = $this->branch();

        $order = $this->confirmBooking([
            ...$this->scheduledPayload($car, $service, $branch),
            'sub_service_ids' => [$subA->id],
        ]);

        $this->actingAsSuperAdmin();
        $this->updateBooking($order->id, ['sub_service_ids' => []])->assertOk();

        $order->refresh();
        $this->assertEquals(0.0, (float) $order->sub_service_price);
        $this->assertEquals(100.0, (float) $order->total_price);
        $this->assertEquals(0, $order->subServices()->count());
    }

    public function test_update_without_sub_service_ids_leaves_the_selection_untouched(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);
        $subA = $this->subServiceFor($service, 20);
        $branch = $this->branch();

        $order = $this->confirmBooking([
            ...$this->scheduledPayload($car, $service, $branch),
            'sub_service_ids' => [$subA->id],
        ]);

        $this->actingAsSuperAdmin();
        // A request that doesn't mention sub_service_ids must not touch them.
        $this->updateBooking($order->id, ['is_vip' => false])->assertOk();

        $order->refresh();
        $this->assertEquals(20.0, (float) $order->sub_service_price);
        $this->assertEquals(1, $order->subServices()->count());
        $this->assertDatabaseHas('order_sub_services', ['order_id' => $order->id, 'sub_service_id' => $subA->id]);
    }

    public function test_update_rejects_a_sub_service_from_a_different_service(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $branch = $this->branch();
        $foreign = $this->subServiceFor($this->service(), 20);

        $order = $this->confirmBooking($this->scheduledPayload($car, $service, $branch));

        $this->actingAsSuperAdmin();
        $this->updateBooking($order->id, ['sub_service_ids' => [$foreign->id]])
            ->assertStatus(422)->assertJsonValidationErrors('sub_service_ids', 'data');
    }

    public function test_update_ignores_fields_outside_the_allowed_set(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $branch = $this->branch();
        $otherBranch = $this->branch();

        $order = $this->confirmBooking($this->scheduledPayload($car, $service, $branch));

        $this->actingAsSuperAdmin();
        $this->updateBooking($order->id, [
            'is_vip' => false,
            'notes' => 'should be ignored',
            'branch_id' => $otherBranch->id,
        ])->assertOk();

        $order->refresh();
        $this->assertNull($order->notes);
        $this->assertEquals($branch->id, $order->branch_id);
    }

    public function test_update_priced_fields_are_blocked_on_a_package_booking(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);
        $branch = $this->branch();
        $userPackage = $this->packageFor($customer, $service);

        $order = $this->confirmBooking([
            ...$this->scheduledPayload($car, $service, $branch),
            'payment_method' => PaymentMethod::PACKAGE->value,
            'user_package_id' => $userPackage->id,
        ]);
        $this->assertEquals($userPackage->id, $order->user_package_id);

        $this->actingAsSuperAdmin();

        $this->updateBooking($order->id, ['is_vip' => true])
            ->assertStatus(422)->assertJsonValidationErrors('payment', 'data');

        $this->updateBooking($order->id, ['booking_type' => true])
            ->assertStatus(422)->assertJsonValidationErrors('payment', 'data');

        $sub = $this->subServiceFor($service, 20);
        $this->updateBooking($order->id, ['sub_service_ids' => [$sub->id]])
            ->assertStatus(422)->assertJsonValidationErrors('payment', 'data');
    }

    public function test_update_reschedule_is_allowed_on_a_package_booking_without_repricing(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);
        $branch = $this->branch();
        $userPackage = $this->packageFor($customer, $service);

        $order = $this->confirmBooking([
            ...$this->scheduledPayload($car, $service, $branch),
            'payment_method' => PaymentMethod::PACKAGE->value,
            'user_package_id' => $userPackage->id,
        ]);
        $totalBefore = (float) $order->total_price;

        $this->actingAsSuperAdmin();
        $when = now()->addDays(4)->startOfMinute();
        $this->updateBooking($order->id, ['scheduled_at' => $when->toDateTimeString()])->assertOk();

        $order->refresh();
        $this->assertEquals($when->toDateTimeString(), $order->scheduled_at->toDateTimeString());
        // A package order is never re-priced, so its settled total is untouched.
        $this->assertEquals($totalBefore, (float) $order->total_price);
    }

    public function test_customer_cannot_reach_the_update_endpoint(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $branch = $this->branch();

        $order = $this->confirmBooking($this->scheduledPayload($car, $service, $branch));

        // Still authenticated as the owning customer, who lacks the edit.order gate.
        $this->updateBooking($order->id, ['is_vip' => true])->assertStatus(403);
    }
}