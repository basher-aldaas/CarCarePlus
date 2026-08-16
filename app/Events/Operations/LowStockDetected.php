<?php

namespace App\Events\Operations;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a material's stock drops to (or below) its configured minimum.
 * Scalars are carried so the alert stays accurate to the crossing moment.
 */
class LowStockDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $inventoryId,
        public int $adminId,
        public ?string $materialName,
        public string $quantity,
        public string $min,
    ) {}
}