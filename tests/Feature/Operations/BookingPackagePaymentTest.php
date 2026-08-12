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
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialUnit;
use App\Models\Order;
use App\Models\Package;
use App\Models\PackageService;
use App\Models\PackageServiceSubService;
use App\Models\Service;
use App\Models\SubService;
use App\Models\User;
use App\Models\UserPackage;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingPackagePaymentTest extends TestCase
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

    private function service(float $basePrice = 100): Service
    {
        $category = Category::create(['name' => 'Category ' . uniqid(), 'name_ar' => 'فئة', 'is_active' => true]);

        return Service::create([
            'category_id' => $category->id,
            'name' => 'Basic Wash',
            'name_ar' => 'غسيل أساسي',
            'description' => 'desc',
            'base_price' => $basePrice,
            'is_vip_available' => false,
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

    private function material(float $unitPrice = 15): Material
    {
        $unit = MaterialUnit::create(['name' => 'Bottle', 'name_ar' => 'زجاجة', 'is_decimal' => false]);

        return Material::create([
            'material_unit_id' => $unit->id,
            'name' => 'Wax',
            'name_ar' => 'شمع',
            'unit_price' => $unitPrice,
            'is_visible_to_customer' => true,
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

    private function stock(Branch $branch, Material $material, float $quantity): Inventory
    {
        return Inventory::create([
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'quantity' => $quantity,
            'min_quantity' => 0,
        ]);
    }

    /**
     * A package covering $service, with $coveredSubServices mapped as
     * sub_service_id => price_override (null = fully free).
     */
    private function packageCovering(Service $service, array $coveredSubServices = []): Package
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

        $packageService = PackageService::create([
            'package_id' => $package->id,
            'service_id' => $service->id,
            'allowed_count' => 1,
        ]);

        foreach ($coveredSubServices as $subServiceId => $priceOverride) {
            PackageServiceSubService::create([
                'package_service_id' => $packageService->id,
                'sub_service_id' => $subServiceId,
                'price_override' => $priceOverride,
                'is_active' => true,
            ]);
        }

        return $package;
    }

    private function userPackageFor(
        User $customer,
        Package $package,
        int $remainingCount = 5,
        UserPackageStatus $status = UserPackageStatus::ACTIVE,
        ?\DateTimeInterface $endDate = null,
    ): UserPackage {
        return UserPackage::create([
            'user_id' => $customer->id,
            'package_id' => $package->id,
            'start_date' => now()->subDay(),
            'end_date' => $endDate ?? now()->addDays(30),
            'remaining_count' => $remainingCount,
            'status' => $status->value,
        ]);
    }

    private function basePayload(Car $car, Service $service, ?Branch $branch = null): array
    {
        return [
            'car_ids' => [$car->id],
            'service_id' => $service->id,
            'branch_id' => $branch?->id,
            'location_lat' => 24.7136,
            'location_lng' => 46.6753,
            'booking_type' => false,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'payment_method' => PaymentMethod::PACKAGE->value,
        ];
    }

    public function test_quote_without_user_package_id_returns_eligible_packages(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $package = $this->packageCovering($service);
        $userPackage = $this->userPackageFor($customer, $package);

        $res = $this->postJson('/api/bookings/quote', $this->basePayload($car, $service));

        $res->assertOk();
        $res->assertJsonPath('data.requires_package_selection', true);
        $res->assertJsonPath('data.eligible_packages.0.id', $userPackage->id);
        $this->assertArrayNotHasKey('quote_token', $res->json('data'));
    }

    public function test_quote_with_selected_package_computes_partial_coverage_and_cash_due(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);

        $coveredFree = $this->subServiceFor($service, 20);
        $coveredPartial = $this->subServiceFor($service, 30);
        $notCovered = $this->subServiceFor($service, 25);
        $material = $this->material(15);
        $branch = $this->branch();
        $this->stock($branch, $material, 5);

        $package = $this->packageCovering($service, [
            $coveredFree->id => null,
            $coveredPartial->id => 10,
        ]);
        $userPackage = $this->userPackageFor($customer, $package);

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service, $branch),
            'sub_service_ids' => [$coveredFree->id, $coveredPartial->id, $notCovered->id],
            'materials' => [['material_id' => $material->id, 'quantity' => 1]],
            'user_package_id' => $userPackage->id,
        ]);

        $res->assertOk();
        $this->assertNotEmpty($res->json('data.quote_token'));
        $this->assertEquals(100.0, (float) $res->json('data.invoice.0.service_price'));
        $this->assertEquals(75.0, (float) $res->json('data.invoice.0.sub_service_price'));
        $this->assertEquals(15.0, (float) $res->json('data.invoice.0.materials_price'));
        $this->assertEquals(190.0, (float) $res->json('data.invoice.0.total_price'));
        $this->assertEquals(140.0, (float) $res->json('data.invoice.0.package_covered_amount'));
        $this->assertEquals(50.0, (float) $res->json('data.invoice.0.cash_due_amount'));
        $this->assertEquals(50.0, (float) $res->json('data.cash_due_total'));

        // remaining_count must not be touched at quote time
        $this->assertEquals(5, $userPackage->fresh()->remaining_count);
    }

    public function test_confirm_deducts_remaining_count_and_records_split_payments(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);

        $coveredFree = $this->subServiceFor($service, 20);
        $notCovered = $this->subServiceFor($service, 25);
        $material = $this->material(15);
        $branch = $this->branch();
        $this->stock($branch, $material, 5);

        $package = $this->packageCovering($service, [$coveredFree->id => null]);
        $userPackage = $this->userPackageFor($customer, $package, remainingCount: 3);

        $quote = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service, $branch),
            'sub_service_ids' => [$coveredFree->id, $notCovered->id],
            'materials' => [['material_id' => $material->id, 'quantity' => 1]],
            'user_package_id' => $userPackage->id,
        ])->json('data');

        $res = $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']]);
        $res->assertOk();

        $this->assertEquals(2, $userPackage->fresh()->remaining_count);

        $order = Order::firstOrFail();
        $this->assertEquals($userPackage->id, $order->user_package_id);
        $this->assertEquals(120.0, (float) $order->package_covered_amount); // 100 service + 20 covered sub
        $this->assertEquals(40.0, (float) $order->cash_due_amount); // 25 uncovered sub + 15 material

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => PaymentMethod::PACKAGE->value,
            'status' => PaymentStatus::PAID->value,
            'amount' => 120.00,
            'package_id' => $package->id,
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => PaymentMethod::CASH->value,
            'status' => PaymentStatus::PENDING->value,
            'amount' => 40.00,
        ]);

        $this->assertDatabaseHas('order_sub_services', [
            'order_id' => $order->id,
            'sub_service_id' => $coveredFree->id,
            'covered_by_package' => 1,
        ]);

        $this->assertDatabaseHas('order_sub_services', [
            'order_id' => $order->id,
            'sub_service_id' => $notCovered->id,
            'covered_by_package' => 0,
        ]);
    }

    public function test_fully_covered_booking_creates_no_cash_payment(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);
        $package = $this->packageCovering($service);
        $userPackage = $this->userPackageFor($customer, $package);

        $quote = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service),
            'user_package_id' => $userPackage->id,
        ])->json('data');

        $this->assertEquals(0.0, $quote['cash_due_total']);

        $res = $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']]);
        $res->assertOk();

        $order = Order::firstOrFail();
        $this->assertEquals(1, $order->payments()->count());
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => PaymentMethod::PACKAGE->value,
            'amount' => 100.00,
        ]);
    }

    public function test_expired_package_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $package = $this->packageCovering($service);
        $userPackage = $this->userPackageFor($customer, $package, endDate: now()->subDay());

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service),
            'user_package_id' => $userPackage->id,
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('user_package_id', 'data');
    }

    public function test_package_not_covering_service_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $otherService = $this->service();
        $package = $this->packageCovering($otherService);
        $userPackage = $this->userPackageFor($customer, $package);

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service),
            'user_package_id' => $userPackage->id,
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('user_package_id', 'data');
    }

    public function test_package_belonging_to_another_customer_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $package = $this->packageCovering($service);

        $otherCustomer = User::factory()->create(['is_active' => true]);
        $otherCustomer->assignRole('customer_personal');
        $foreignUserPackage = $this->userPackageFor($otherCustomer, $package);

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service),
            'user_package_id' => $foreignUserPackage->id,
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('user_package_id', 'data');
    }

    public function test_insufficient_remaining_uses_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $package = $this->packageCovering($service);
        $userPackage = $this->userPackageFor($customer, $package, remainingCount: 0);

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service),
            'user_package_id' => $userPackage->id,
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('user_package_id', 'data');
    }
}