<?php

namespace App\Observers;

use App\Events\Operations\LowStockDetected;
use App\Models\Branch;
use App\Models\Inventory;

class InventoryObserver
{
    /**
     * After a stock quantity change, alert the branch admin when the material
     * has dropped to (or below) its configured minimum. Fires only on the
     * downward crossing — stock was above the minimum before and is at/below
     * it after — so the admin is notified once per depletion, not on every
     * later movement while it stays low. Covers every path that mutates
     * inventory quantity (inventory transactions, order fulfilment, etc.),
     * since they all persist through the Inventory model.
     */
    public function updated(Inventory $inventory): void
    {
        if (! $inventory->wasChanged('quantity')) {
            return;
        }

        $min = (float) $inventory->min_quantity;

        // No threshold configured — nothing to alert on.
        if ($min <= 0) {
            return;
        }

        $before = (float) $inventory->getOriginal('quantity');
        $after = (float) $inventory->quantity;

        // Only the downward crossing: above the minimum before, at/below it now.
        if ($before <= $min || $after > $min) {
            return;
        }

        $adminId = Branch::query()->where('id', $inventory->branch_id)->value('admin_id');

        if ($adminId === null) {
            return;
        }

        $inventory->loadMissing('material');

        LowStockDetected::dispatch(
            $inventory->id,
            $adminId,
            $inventory->material?->name,
            $this->trim($after),
            $this->trim($min),
        );
    }

    /**
     * Format a stock amount without trailing zeros (e.g. 5.00 -> "5",
     * 2.50 -> "2.5").
     */
    private function trim(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}