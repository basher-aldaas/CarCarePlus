<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case IN = 'in';
    case OUT = 'out';
    case TRANSFER = 'transfer';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        return __("device_status.{$this->value}", locale: $locale);
    }
}
