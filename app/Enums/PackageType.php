<?php

namespace App\Enums;

enum PackageType: string
{
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case COMPANY = 'company';

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
