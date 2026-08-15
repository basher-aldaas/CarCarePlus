<?php

namespace Tests\Feature\Operations;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The low-stock alert is raised by {@see \App\Observers\InventoryObserver},
 * which fires on any Inventory quantity change regardless of the code path
 * (inventory transactions, order fulfilment, etc.). These tests exercise the
 * observer directly through the model.
 */
class InventoryLowStockNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function branchWithAdmin(): array
    {
        $admin = User::factory()->create(['is_active' => true]);

        $branch = Branch::create([
            'admin_id' => $admin->id,
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

        return [$branch, $admin];
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

    private function stock(Branch $branch, Material $material, float $quantity, float $min): Inventory
    {
        return Inventory::create([
            'branch_id' => $branch->id,
            'material_id' => $material->id,
            'quantity' => $quantity,
            'min_quantity' => $min,
        ]);
    }

    public function test_dropping_to_minimum_notifies_the_branch_admin(): void
    {
        [$branch, $admin] = $this->branchWithAdmin();
        $material = $this->material();
        $inventory = $this->stock($branch, $material, quantity: 10, min: 5);

        // 10 -> 4, crosses below the minimum of 5.
        $inventory->update(['quantity' => 4]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'type' => 'warning',
            'reference_type' => 'inventory',
            'reference_id' => $inventory->id,
        ]);
    }

    public function test_dropping_exactly_to_minimum_notifies(): void
    {
        [$branch, $admin] = $this->branchWithAdmin();
        $material = $this->material();
        $inventory = $this->stock($branch, $material, quantity: 10, min: 5);

        // 10 -> 5, reaches the minimum exactly.
        $inventory->update(['quantity' => 5]);

        $this->assertDatabaseHas('notifications', ['user_id' => $admin->id]);
    }

    public function test_staying_above_minimum_does_not_notify(): void
    {
        [$branch] = $this->branchWithAdmin();
        $material = $this->material();
        $inventory = $this->stock($branch, $material, quantity: 20, min: 5);

        // 20 -> 12, still above the minimum.
        $inventory->update(['quantity' => 12]);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_no_notification_when_no_minimum_is_configured(): void
    {
        [$branch] = $this->branchWithAdmin();
        $material = $this->material();
        $inventory = $this->stock($branch, $material, quantity: 10, min: 0);

        $inventory->update(['quantity' => 1]);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_already_below_minimum_does_not_notify_again(): void
    {
        [$branch] = $this->branchWithAdmin();
        $material = $this->material();
        // Already below the minimum before this movement.
        $inventory = $this->stock($branch, $material, quantity: 4, min: 5);

        // 4 -> 2, no downward crossing (was already below).
        $inventory->update(['quantity' => 2]);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_restocking_does_not_notify(): void
    {
        [$branch] = $this->branchWithAdmin();
        $material = $this->material();
        $inventory = $this->stock($branch, $material, quantity: 3, min: 5);

        // 3 -> 20, quantity increased.
        $inventory->update(['quantity' => 20]);

        $this->assertDatabaseCount('notifications', 0);
    }
}