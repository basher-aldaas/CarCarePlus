<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    /**
     * Opening stock levels per material (id from MaterialSeeder), applied to every branch.
     */
    public const OPENING_LEVELS = [
        1 => ['quantity' => 50, 'min_quantity' => 10],  // Synthetic Engine Oil 5W-30
        2 => ['quantity' => 100, 'min_quantity' => 20], // Premium Car Shampoo
        3 => ['quantity' => 200, 'min_quantity' => 30], // Microfiber Towel (Ultra Soft)
    ];

    public function run(): void
    {
        $branches = DB::table('branches')->get(['id']);
        $materials = DB::table('materials')->get(['id']);

        if ($branches->isEmpty() || $materials->isEmpty()) {
            return;
        }

        foreach ($branches as $branch) {
            foreach ($materials as $material) {
                $levels = self::OPENING_LEVELS[$material->id] ?? ['quantity' => 50, 'min_quantity' => 10];

                DB::table('inventories')->updateOrInsert(
                    ['branch_id' => $branch->id, 'material_id' => $material->id],
                    [
                        'quantity' => $levels['quantity'],
                        'min_quantity' => $levels['min_quantity'],
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
