<?php

namespace Tests\Feature\Operations;

use App\Models\Branch;
use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarType;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialUnit;
use App\Models\Order;
use App\Models\Service;
use App\Models\SubService;
use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingQuoteSubServiceMaterialTest extends TestCase
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

    private function service(): Service
    {
        $category = Category::create(['name' => 'Category ' . uniqid(), 'name_ar' => 'فئة', 'is_active' => true]);

        return Service::create([
            'category_id' => $category->id,
            'name' => 'Basic Wash',
            'name_ar' => 'غسيل أساسي',
            'description' => 'desc',
            'base_price' => 100,
            'is_vip_available' => false,
            'duration_minutes' => 30,
        ]);
    }

    private function subServiceFor(Service $service, float $price = 20, bool $active = true): SubService
    {
        return SubService::create([
            'service_id' => $service->id,
            'name' => 'Interior Cleaning',
            'name_ar' => 'تنظيف داخلي',
            'price' => $price,
            'is_active' => $active,
        ]);
    }

    private function material(float $unitPrice = 15, bool $visible = true, bool $active = true): Material
    {
        $unit = MaterialUnit::create(['name' => 'Bottle', 'name_ar' => 'زجاجة', 'is_decimal' => false]);

        return Material::create([
            'material_unit_id' => $unit->id,
            'name' => 'Wax',
            'name_ar' => 'شمع',
            'unit_price' => $unitPrice,
            'is_visible_to_customer' => $visible,
            'is_active' => $active,
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

    private function basePayload(Car $car, Service $service, ?Branch $branch = null): array
    {
        return [
            'car_ids' => [$car->id],
            'service_id' => $service->id,
            'branch_id' => $branch?->id,
            'booking_type' => false,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'payment_method' => 'cash',
        ];
    }

    public function test_quote_prices_selected_sub_services_and_materials_additively(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $subService = $this->subServiceFor($service, 20);
        $material = $this->material(15);
        $branch = $this->branch();
        $this->stock($branch, $material, 5);

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service, $branch),
            'sub_service_ids' => [$subService->id],
            'materials' => [['material_id' => $material->id, 'quantity' => 2]],
        ]);

        $res->assertOk();
        $res->assertJsonPath('data.total_price', 100 + 20 + 30);
        $res->assertJsonPath('data.cars.0.service_price', 100.0);
        $res->assertJsonPath('data.cars.0.sub_service_price', 20.0);
        $res->assertJsonPath('data.cars.0.materials_price', 30.0);
        $res->assertJsonPath('data.cars.0.total_price', 150.0);
    }

    public function test_confirm_persists_order_sub_services_and_materials(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $subService = $this->subServiceFor($service, 20);
        $material = $this->material(15);
        $branch = $this->branch();
        $this->stock($branch, $material, 5);

        $quote = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service, $branch),
            'sub_service_ids' => [$subService->id],
            'materials' => [['material_id' => $material->id, 'quantity' => 2]],
        ])->json('data');

        $res = $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']]);

        $res->assertOk();

        $order = Order::firstOrFail();
        $this->assertEquals(100.0, (float) $order->service_price);
        $this->assertEquals(20.0, (float) $order->sub_service_price);
        $this->assertEquals(30.0, (float) $order->materials_price);
        $this->assertEquals(150.0, (float) $order->total_price);

        $this->assertDatabaseHas('order_sub_services', [
            'order_id' => $order->id,
            'sub_service_id' => $subService->id,
            'price' => 20.00,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_materials', [
            'order_id' => $order->id,
            'material_id' => $material->id,
            'requested_by' => $customer->id,
            'quantity' => 2,
            'unit_price' => 15.00,
            'total_price' => 30.00,
            'status' => 'approved',
        ]);
        $this->assertNotNull($order->materials()->first()->approved_at);
    }

    public function test_no_selections_leaves_new_price_columns_at_zero(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();

        $quote = $this->postJson('/api/bookings/quote', $this->basePayload($car, $service))->json('data');
        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        $order = Order::firstOrFail();
        $this->assertEquals(0.0, (float) $order->sub_service_price);
        $this->assertEquals(0.0, (float) $order->materials_price);
        $this->assertEquals(100.0, (float) $order->total_price);
    }

    public function test_sub_service_belonging_to_a_different_service_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $otherService = $this->service();
        $foreignSubService = $this->subServiceFor($otherService, 20);

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service),
            'sub_service_ids' => [$foreignSubService->id],
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('sub_service_ids', 'data');
    }

    public function test_inactive_sub_service_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $inactiveSubService = $this->subServiceFor($service, 20, active: false);

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service),
            'sub_service_ids' => [$inactiveSubService->id],
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('sub_service_ids', 'data');
    }

    public function test_material_not_visible_to_customer_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $hiddenMaterial = $this->material(15, visible: false);
        $branch = $this->branch();

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service, $branch),
            'materials' => [['material_id' => $hiddenMaterial->id, 'quantity' => 1]],
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('materials', 'data');
    }

    public function test_inactive_material_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $inactiveMaterial = $this->material(15, visible: true, active: false);
        $branch = $this->branch();

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service, $branch),
            'materials' => [['material_id' => $inactiveMaterial->id, 'quantity' => 1]],
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('materials', 'data');
    }

    public function test_duplicate_material_ids_are_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $material = $this->material(15);
        $branch = $this->branch();

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service, $branch),
            'materials' => [
                ['material_id' => $material->id, 'quantity' => 1],
                ['material_id' => $material->id, 'quantity' => 2],
            ],
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('materials.0.material_id', 'data');
    }

    public function test_zero_quantity_material_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $material = $this->material(15);

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service),
            'materials' => [['material_id' => $material->id, 'quantity' => 0]],
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('materials.quantity', 'data');
    }

    public function test_material_requires_a_branch(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $material = $this->material(15);

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service),
            'materials' => [['material_id' => $material->id, 'quantity' => 1]],
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('branch_id', 'data');
    }

    public function test_material_exceeding_branch_stock_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $material = $this->material(15);
        $branch = $this->branch();
        $this->stock($branch, $material, 1);

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service, $branch),
            'materials' => [['material_id' => $material->id, 'quantity' => 2]],
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('materials', 'data');
    }

    public function test_confirm_deducts_inventory_and_logs_transaction_once_paid(): void
    {
        $customer = $this->customer();
        Wallet::create(['user_id' => $customer->id, 'balance' => 1000]);
        $car = $this->carFor($customer);
        $service = $this->service();
        $material = $this->material(15);
        $branch = $this->branch();
        $this->stock($branch, $material, 5);

        $quote = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service, $branch),
            'payment_method' => 'wallet',
            'materials' => [['material_id' => $material->id, 'quantity' => 2]],
        ])->json('data');

        $res = $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']]);
        $res->assertOk();

        $order = Order::firstOrFail();

        $this->assertDatabaseHas('inventories', [
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseHas('inventory_transactions', [
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'type' => 'out',
            'quantity' => 2,
            'quantity_before' => 5,
            'quantity_after' => 3,
        ]);

        $this->assertDatabaseHas('order_materials', [
            'order_id' => $order->id,
            'material_id' => $material->id,
            'status' => 'used',
        ]);
    }
}