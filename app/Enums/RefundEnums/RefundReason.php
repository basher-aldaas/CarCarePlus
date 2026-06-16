<?php

namespace App\Enums\RefundEnums;

enum RefundReason: string
{
    case CANCELLED_ORDER = 'cancelled_order';
    case SERVICE_NOT_PROVIDED = 'service_not_provided';
    case DUPLICATE_PAYMENT = 'duplicate_payment';

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
