<?php

namespace App\Enums;

use function Laravel\Prompts\select;

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

    public function label(): string
    {
        return match ($this) {
            self::MAINTENANCE => 'Maintenance',
            self::RECOMMENDATION => 'Recommendation',
            self::WARNING => 'Warning',
            self::PROMOTION => 'Promotion',
            self::UPSELL => 'Upsell',
            self::DIAGNOSIS =>'Diagnosis',
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::MAINTENANCE => 'صيانة',
            self::RECOMMENDATION => 'توصية',
            self::WARNING => 'تحذير',
            self::PROMOTION => 'عرض ترويجي',
            self::UPSELL => 'اقتراح خدمة إضافية',
            self::DIAGNOSIS => 'تشخيص اداء' ,
        };
    }
}
