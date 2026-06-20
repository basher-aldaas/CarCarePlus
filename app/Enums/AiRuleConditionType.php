<?php

namespace App\Enums;

enum AiRuleConditionType: string
{
    case MAINTENANCE = 'maintenance';
    case RECOMMENDATION = 'recommendation';
    case WARNING = 'warning';
    case PROMOTION = 'promotion';
    case UPSELL = 'upsell';
    case DIAGNOSIS = 'diagnosis';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return __("ai_rule_condition_type.{$this->value}", locale: $locale);
    }
}
