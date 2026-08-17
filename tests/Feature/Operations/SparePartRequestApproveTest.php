<?php

namespace Tests\Feature\Operations;

use App\Enums\EmployeeType;
use App\Enums\InventoryTransactionType;
use App\Enums\OrderEnums\OrderStatus;
use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Enums\PaymentEnums\PaymentType;
use App\Enums\SparePartRequestStatus;
use App\Models\Branch;
use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarType;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialUnit;
use App\Models\Order;
use App\Models\Service;
use App\Models\SparePartRequest;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SparePartRequestApproveTest extends TestCase
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

        return $user;
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

    private function employeeFor(Branch $branch): Employee
    {
        $user = User::factory()->create(['is_active' => true]);

        return Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'type' => EmployeeType::MECHANIC->value,
            'is_active' => true,
        ]);
    }

    private function material(float $unitPrice = 15): Material
    {
        $unit = MaterialUnit::create(['name' => 'Bottle', 'name_ar' => 'زجاجة', 'is_decimal' => false]);

        return Material::create([
            'material_unit_id' => $unit->id,
            'name' => 'Brake Pad',
            'name_ar' => 'فحمات',
            'unit_price' => $unitPrice,
            'is_visible_to_customer' => true,
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

    private function orderFor(User $customer, Branch $branch): Order
    {
        $brand = CarBrand::create(['name' => 'Brand ' . uniqid(), 'is_active' => true]);
        $carType = CarType::create(['name' => 'Sedan', 'name_ar' => 'سيدان', 'price_multiplier' => 1, 'is_active' => true]);
        $car = Car::create([
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

        $category = Category::create(['name' => 'Category ' . uniqid(), 'name_ar' => 'فئة', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id,
            'name' => 'Repair',
            'name_ar' => 'إصلاح',
            'description' => 'desc',
            'base_price' => 100,
            'is_vip_available' => false,
            'duration_minutes' => 30,
        ]);

        return Order::create([
            'customer_id' => $customer->id,
            'car_id' => $car->id,
            'category_id' => $category->id,
            'service_id' => $service->id,
            'branch_id' => $branch->id,
            'booking_type' => false,
            'status' => OrderStatus::IN_PROGRESS->value,
        ]);
    }

    private function sparePartRequest(Order $order, Employee $employee, Material $material, int $quantity = 2): SparePartRequest
    {
        return SparePartRequest::create([
            'order_id' => $order->id,
            'employee_id' => $employee->id,
            'material_id' => $material->id,
            'quantity' => $quantity,
            'status' => SparePartRequestStatus::PENDING->value,
        ]);
    }

    public function test_approve_deducts_inventory_logs_transaction_and_records_payment(): void
    {
        $customer = $this->customer();
        $branch = $this->branch();
        $employee = $this->employeeFor($branch);
        $material = $this->material(15);
        $this->stock($branch, $material, 10);
        $order = $this->orderFor($customer, $branch);
        $request = $this->sparePartRequest($order, $employee, $material, quantity: 2);

        Sanctum::actingAs($customer);

        $res = $this->postJson("/api/spare-part-requests/{$request->id}/approve", ['notes' => 'ok']);

        $res->assertOk();

        $this->assertEquals(
            SparePartRequestStatus::APPROVED,
            $request->fresh()->status,
        );

        // Inventory deducted 2 from 10.
        $this->assertDatabaseHas('inventories', [
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'quantity' => 8,
        ]);

        // OUT movement logged with before/after snapshot.
        $this->assertDatabaseHas('inventory_transactions', [
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'type' => InventoryTransactionType::OUT->value,
            'quantity' => 2,
            'quantity_before' => 10,
            'quantity_after' => 8,
            'reference_id' => (string) $request->id,
        ]);

        // Isolated PENDING cash payment: 2 x 15 = 30.
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'spare_part_request_id' => $request->id,
            'type' => PaymentType::SPARE->value,
            'method' => PaymentMethod::CASH->value,
            'status' => PaymentStatus::PENDING->value,
            'amount' => 30.00,
        ]);

        // Isolation: SPARE payment must not award loyalty points.
        $this->assertDatabaseMissing('points_transactions', [
            'reference_type' => 'order',
            'reference_id' => $order->id,
        ]);
    }

    public function test_re_approving_does_not_deduct_or_charge_twice(): void
    {
        $customer = $this->customer();
        $branch = $this->branch();
        $employee = $this->employeeFor($branch);
        $material = $this->material(15);
        $this->stock($branch, $material, 10);
        $order = $this->orderFor($customer, $branch);
        $request = $this->sparePartRequest($order, $employee, $material, quantity: 2);

        Sanctum::actingAs($customer);

        $this->postJson("/api/spare-part-requests/{$request->id}/approve")->assertOk();
        $this->postJson("/api/spare-part-requests/{$request->id}/approve")->assertOk();

        // Stock deducted only once.
        $this->assertEquals(8, (int) $material->inventories()->where('branch_id', $branch->id)->value('quantity'));
        $this->assertEquals(1, Order::find($order->id)->payments()->count());
        $this->assertEquals(1, \App\Models\Payment::where('spare_part_request_id', $request->id)->count());
    }

    public function test_approve_is_rejected_when_stock_is_insufficient(): void
    {
        $customer = $this->customer();
        $branch = $this->branch();
        $employee = $this->employeeFor($branch);
        $material = $this->material(15);
        $this->stock($branch, $material, 1);
        $order = $this->orderFor($customer, $branch);
        $request = $this->sparePartRequest($order, $employee, $material, quantity: 2);

        Sanctum::actingAs($customer);

        $this->postJson("/api/spare-part-requests/{$request->id}/approve")
            ->assertStatus(422);

        // Nothing settled: status untouched, no stock change, no payment.
        $this->assertEquals(SparePartRequestStatus::PENDING, $request->fresh()->status);
        $this->assertEquals(1, (int) $material->inventories()->where('branch_id', $branch->id)->value('quantity'));
        $this->assertDatabaseMissing('payments', ['spare_part_request_id' => $request->id]);
    }

    public function test_a_stranger_cannot_approve_another_customers_request(): void
    {
        $customer = $this->customer();
        $branch = $this->branch();
        $employee = $this->employeeFor($branch);
        $material = $this->material(15);
        $this->stock($branch, $material, 10);
        $order = $this->orderFor($customer, $branch);
        $request = $this->sparePartRequest($order, $employee, $material);

        $stranger = $this->customer();
        Sanctum::actingAs($stranger);

        $this->postJson("/api/spare-part-requests/{$request->id}/approve")
            ->assertStatus(403);

        $this->assertEquals(SparePartRequestStatus::PENDING, $request->fresh()->status);
        $this->assertDatabaseMissing('payments', ['spare_part_request_id' => $request->id]);
    }
}