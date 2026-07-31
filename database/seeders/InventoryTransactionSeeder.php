<?php

namespace Database\Seeders;

use App\Enums\InventoryTransactionType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryTransactionSeeder extends Seeder
{
    /**
     * Records the opening-balance "in" transaction that matches the stock
     * levels InventorySeeder wrote, so the ledger and Inventory.quantity agree.
     */
    public function run(): void
    {
        $creator = DB::table('users')->where('email', 'superadmin@system.com')->first();

        if (! $creator) {
            return;
        }

        $branches = DB::table('branches')->get(['id']);
        $materials = DB::table('materials')->get(['id']);

        if ($branches->isEmpty() || $materials->isEmpty()) {
            return;
        }

        foreach ($branches as $branch) {
            foreach ($materials as $material) {
                $alreadySeeded = DB::table('inventory_transactions')
                    ->where('branch_id', $branch->id)
                    ->where('material_id', $material->id)
                    ->where('note', 'Opening balance')
                    ->exists();

                if ($alreadySeeded) {
                    continue;
                }

                $levels = InventorySeeder::OPENING_LEVELS[$material->id] ?? ['quantity' => 50, 'min_quantity' => 10];

                DB::table('inventory_transactions')->insert([
                    'branch_id' => $branch->id,
                    'material_id' => $material->id,
                    'created_by' => $creator->id,
                    'type' => InventoryTransactionType::IN->value,
                    'quantity' => $levels['quantity'],
                    'quantity_before' => 0,
                    'quantity_after' => $levels['quantity'],
                    'reference_id' => null,
                    'note' => 'Opening balance',
                    'created_at' => now(),
                ]);
            }
        }
    }
}
