<?php

namespace App\Enums\WalletTransactionEnums;

enum WalletTransactionReason: string
{
    case ORDER_PAYMENT = 'order_payment';
    case REFUND = 'refund';
    case TOPUP = 'topup';
    case ADJUSTMENT = 'adjustment';

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
