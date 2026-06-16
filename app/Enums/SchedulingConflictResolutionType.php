<?php

namespace App\Enums;

enum SchedulingConflictResolutionType: string
{
    case RESCHEDULE_ORDER_1 = 'reschedule_order_1';
    case RESCHEDULE_ORDER_2 = 'reschedule_order_2';
    case CANCEL_ORDER_1 = 'cancel_order_1';
    case CANCEL_ORDER_2 = 'cancel_order_2';
    case MERGED = 'merged';
    case MANUAL_OVERRIDE = 'manual_override';

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
