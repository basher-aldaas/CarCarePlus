<?php

namespace Tests\Feature\Operations;

use App\Enums\InventoryTransactionType;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialUnit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InventoryTransactionStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('super_admin');
        Sanctum::actingAs($user);

        return $user;
    }

    private function admin(bool $active = true): User
    {
        $user = User::factory()->create(['is_active' => $active]);
        $user->assignRole('admin');
        Sanctum::actingAs($user);

        return $user;
    }

    private function customer(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('customer_personal');
        Sanctum::actingAs($user);

        return $user;
    }

    private function branch(?User $admin = null): Branch
    {
        return Branch::create([
            'admin_id' => $admin?->id ?? User::factory()->create()->id,
            'name' => 'Branch ' . uniqid(),
            'name_ar' => 'فرع',
            'city' => 'City',
            'address' => 'Address',
            'latitude' => 24.0,
            'longitude' => 46.0,
            'phone' => '0500000000',
            'is_active' => true,
            'working_hours' => json_encode(['mon' => '08:00-20:00']),
            'is_24h' => false,
        ]);
    }

    private function material(): Material
    {
        $unit = MaterialUnit::create([
            'name' => 'Liter',
            'name_ar' => 'لتر',
            'is_decimal' => true,
        ]);

        return Material::create([
            'material_unit_id' => $unit->id,
            'name' => 'Oil',
            'name_ar' => 'زيت',
            'unit_price' => 10,
            'is_active' => true,
        ]);
    }

    private function setStock(Branch $branch, Material $material, float $quantity): void
    {
        Inventory::create([
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'quantity' => $quantity,
            'min_quantity' => 0,
        ]);
    }

    // ---------------------------------------------------------------
    // Success cases
    // ---------------------------------------------------------------

    public function test_super_admin_can_record_an_in_transaction(): void
    {
        $this->superAdmin();
        $branch = $this->branch();
        $material = $this->material();

        $res = $this->postJson('/api/inventory-transactions', [
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'type' => InventoryTransactionType::IN->value,
            'quantity' => 20,
        ]);

        $res->assertOk()->assertJsonPath('status', 1);
        $this->assertEquals(20, Inventory::where('branch_id', $branch->id)->where('material_id', $material->id)->value('quantity'));
    }

    public function test_super_admin_can_record_an_out_transaction_with_sufficient_stock(): void
    {
        $this->superAdmin();
        $branch = $this->branch();
        $material = $this->material();
        $this->setStock($branch, $material, 50);

        $res = $this->postJson('/api/inventory-transactions', [
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'type' => InventoryTransactionType::OUT->value,
            'quantity' => 30,
        ]);

        $res->assertOk()->assertJsonPath('status', 1);
        $this->assertEquals(20, Inventory::where('branch_id', $branch->id)->where('material_id', $material->id)->value('quantity'));
    }

    public function test_super_admin_can_record_a_transfer_out_and_it_books_both_legs(): void
    {
        $this->superAdmin();
        $source = $this->branch();
        $destination = $this->branch();
        $material = $this->material();
        $this->setStock($source, $material, 50);

        $res = $this->postJson('/api/inventory-transactions', [
            'branch_id' => $source->id,
            'destination_branch_id' => $destination->id,
            'material_id' => $material->id,
            'type' => InventoryTransactionType::TRANSFER_OUT->value,
            'quantity' => 15,
        ]);

        $res->assertOk()->assertJsonPath('status', 1);
        $res->assertJsonPath('data.type', InventoryTransactionType::TRANSFER_OUT->value);

        $this->assertEquals(35, Inventory::where('branch_id', $source->id)->where('material_id', $material->id)->value('quantity'));
        $this->assertEquals(15, Inventory::where('branch_id', $destination->id)->where('material_id', $material->id)->value('quantity'));

        $this->assertDatabaseHas('inventory_transactions', [
            'branch_id' => $source->id,
            'destination_branch_id' => $destination->id,
            'type' => InventoryTransactionType::TRANSFER_OUT->value,
        ]);
        $this->assertDatabaseHas('inventory_transactions', [
            'branch_id' => $destination->id,
            'destination_branch_id' => $source->id,
            'type' => InventoryTransactionType::TRANSFER_IN->value,
        ]);
    }

    public function test_admin_can_record_a_transaction_for_his_own_branch(): void
    {
        $admin = $this->admin();
        $branch = $this->branch($admin);
        $material = $this->material();

        $res = $this->postJson('/api/inventory-transactions', [
            'material_id' => $material->id,
            'type' => InventoryTransactionType::IN->value,
            'quantity' => 10,
        ]);

        $res->assertOk()->assertJsonPath('status', 1);
        $this->assertEquals(10, Inventory::where('branch_id', $branch->id)->where('material_id', $material->id)->value('quantity'));
    }

    public function test_admin_submitted_branch_id_is_ignored_and_his_own_branch_is_used(): void
    {
        $admin = $this->admin();
        $ownBranch = $this->branch($admin);
        $otherBranch = $this->branch();
        $material = $this->material();

        $res = $this->postJson('/api/inventory-transactions', [
            'branch_id' => $otherBranch->id,
            'material_id' => $material->id,
            'type' => InventoryTransactionType::IN->value,
            'quantity' => 10,
        ]);

        $res->assertOk();
        $this->assertEquals(10, Inventory::where('branch_id', $ownBranch->id)->where('material_id', $material->id)->value('quantity'));
        $this->assertDatabaseMissing('inventories', ['branch_id' => $otherBranch->id, 'material_id' => $material->id]);
    }

    // ---------------------------------------------------------------
    // Failure cases
    // ---------------------------------------------------------------

    public function test_guest_cannot_create_a_transaction(): void
    {
        $branch = $this->branch();
        $material = $this->material();

        $this->postJson('/api/inventory-transactions', [
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'type' => InventoryTransactionType::IN->value,
            'quantity' => 10,
        ])->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_create_a_transaction(): void
    {
        $this->customer();
        $branch = $this->branch();
        $material = $this->material();

        $this->postJson('/api/inventory-transactions', [
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'type' => InventoryTransactionType::IN->value,
            'quantity' => 10,
        ])->assertForbidden();
    }

    public function test_inactive_admin_cannot_create_a_transaction(): void
    {
        $admin = $this->admin(active: false);
        $branch = $this->branch($admin);
        $material = $this->material();

        $this->postJson('/api/inventory-transactions', [
            'material_id' => $material->id,
            'type' => InventoryTransactionType::IN->value,
            'quantity' => 10,
        ])->assertForbidden();
    }

    public function test_admin_without_assigned_branch_is_rejected(): void
    {
        $this->admin();
        $material = $this->material();

        $res = $this->postJson('/api/inventory-transactions', [
            'material_id' => $material->id,
            'type' => InventoryTransactionType::IN->value,
            'quantity' => 10,
        ]);

        $res->assertForbidden()->assertJsonPath('status', 0);
    }

    public function test_out_transaction_fails_with_insufficient_stock(): void
    {
        $this->superAdmin();
        $branch = $this->branch();
        $material = $this->material();
        $this->setStock($branch, $material, 5);

        $res = $this->postJson('/api/inventory-transactions', [
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'type' => InventoryTransactionType::OUT->value,
            'quantity' => 10,
        ]);

        $res->assertStatus(422)->assertJsonPath('status', 0);
        $this->assertEquals(5, Inventory::where('branch_id', $branch->id)->where('material_id', $material->id)->value('quantity'));
        $this->assertDatabaseCount('inventory_transactions', 0);
    }

    public function test_transfer_out_fails_with_insufficient_stock_and_does_not_touch_destination(): void
    {
        $this->superAdmin();
        $source = $this->branch();
        $destination = $this->branch();
        $material = $this->material();
        $this->setStock($source, $material, 5);

        $res = $this->postJson('/api/inventory-transactions', [
            'branch_id' => $source->id,
            'destination_branch_id' => $destination->id,
            'material_id' => $material->id,
            'type' => InventoryTransactionType::TRANSFER_OUT->value,
            'quantity' => 10,
        ]);

        $res->assertStatus(422)->assertJsonPath('status', 0);
        $this->assertDatabaseMissing('inventories', ['branch_id' => $destination->id, 'material_id' => $material->id]);
        $this->assertDatabaseCount('inventory_transactions', 0);
    }

    public function test_transfer_out_fails_when_destination_equals_source_branch(): void
    {
        $this->superAdmin();
        $branch = $this->branch();
        $material = $this->material();
        $this->setStock($branch, $material, 50);

        $res = $this->postJson('/api/inventory-transactions', [
            'branch_id' => $branch->id,
            'destination_branch_id' => $branch->id,
            'material_id' => $material->id,
            'type' => InventoryTransactionType::TRANSFER_OUT->value,
            'quantity' => 10,
        ]);

        $res->assertStatus(422)->assertJsonPath('status', 0);
    }

    public function test_transfer_out_requires_destination_branch_id(): void
    {
        $this->superAdmin();
        $branch = $this->branch();
        $material = $this->material();

        $res = $this->postJson('/api/inventory-transactions', [
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'type' => InventoryTransactionType::TRANSFER_OUT->value,
            'quantity' => 10,
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('destination_branch_id', 'data');
    }

    public function test_destination_branch_id_is_prohibited_for_non_transfer_types(): void
    {
        $this->superAdmin();
        $branch = $this->branch();
        $destination = $this->branch();
        $material = $this->material();

        $res = $this->postJson('/api/inventory-transactions', [
            'branch_id' => $branch->id,
            'destination_branch_id' => $destination->id,
            'material_id' => $material->id,
            'type' => InventoryTransactionType::IN->value,
            'quantity' => 10,
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('destination_branch_id', 'data');
    }

    public function test_client_cannot_submit_transfer_in_directly(): void
    {
        $this->superAdmin();
        $branch = $this->branch();
        $material = $this->material();

        $res = $this->postJson('/api/inventory-transactions', [
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'type' => InventoryTransactionType::TRANSFER_IN->value,
            'quantity' => 10,
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('type', 'data');
    }

    public function test_super_admin_must_submit_branch_id(): void
    {
        $this->superAdmin();
        $material = $this->material();

        $res = $this->postJson('/api/inventory-transactions', [
            'material_id' => $material->id,
            'type' => InventoryTransactionType::IN->value,
            'quantity' => 10,
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('branch_id', 'data');
    }

    public function test_material_id_is_required(): void
    {
        $this->superAdmin();
        $branch = $this->branch();

        $res = $this->postJson('/api/inventory-transactions', [
            'branch_id' => $branch->id,
            'type' => InventoryTransactionType::IN->value,
            'quantity' => 10,
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('material_id', 'data');
    }

    public function test_quantity_must_be_positive(): void
    {
        $this->superAdmin();
        $branch = $this->branch();
        $material = $this->material();

        $res = $this->postJson('/api/inventory-transactions', [
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'type' => InventoryTransactionType::IN->value,
            'quantity' => 0,
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('quantity', 'data');
    }

    public function test_type_must_be_a_valid_enum_value(): void
    {
        $this->superAdmin();
        $branch = $this->branch();
        $material = $this->material();

        $res = $this->postJson('/api/inventory-transactions', [
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'type' => 'not-a-real-type',
            'quantity' => 10,
        ]);

        $res->assertStatus(422)->assertJsonValidationErrors('type', 'data');
    }
}