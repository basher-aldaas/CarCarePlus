<?php

namespace App\Enums\RefundEnums;

enum RefundDestination: string
{
    case WALLET = 'wallet';
    case ORIGINAL_METHOD = 'original_method';

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
