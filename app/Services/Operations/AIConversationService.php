<?php

namespace App\Services\Operations;

class AIConversationService
{
    private array $questions = [

        "متى بدأت المشكلة؟",

        "هل تظهر المشكلة دائماً أم أحياناً؟",

        "هل يوجد صوت غير طبيعي مع المشكلة؟",

        "هل تظهر لمبة تحذير في لوحة العدادات؟",

        "هل المشكلة أثناء القيادة أم أثناء تشغيل السيارة؟",

        "هل أجريت صيانة مؤخراً؟"

    ];


    public function nextQuestion(array $history): ?string
    {
        foreach ($this->questions as $question) {

            $found = collect($history)
                ->contains(fn ($item) =>
                    $item['question'] === $question
                );

            if (!$found) {
                return $question;
            }
        }

        return null;
    }
}
