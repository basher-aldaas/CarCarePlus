<?php

namespace App\Enums\PaymentEnums;

enum PaymentType: string
{
    case ORDER = 'order';
    case PACKAGE = 'package';
    case WALLET_TOPUP = 'wallet_topup';

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
