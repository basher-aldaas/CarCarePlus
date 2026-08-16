<?php

namespace App\Services\Operations;

use App\Models\Order;

class AIDiagnosisService
{
    public function __construct(
        protected AiRuleService $aiRuleService,
        protected OpenRouterService $openRouterService,
        protected AIRecommendationService $recommendationService
    ) {
    }

    public function diagnose(?int $orderId,string $problem,array $history): array
    {
        /*
        |--------------------------------------------------------------------------
        | استخراج معلومات السيارة من Order
        |--------------------------------------------------------------------------
        */

        $brandId = null;
        $carType = null;
        $fuelType = null;

        if ($orderId) {

            $order = Order::with([
                'car.brand',
                'car.carType',
            ])->find($orderId);

            if ($order && $order->car) {

                $brandId = $order->car->brand_id;

                /*
                 * لا نرسل car_type_id إلى AiRule
                 * لأن ai_rules.car_type حالياً يستخدم CarTypeSize enum.
                 */
                $carType = $order->car->carType?->name;

                $fuelType = $order->car->fuel_type?->value
                    ?? $order->car->fuel_type;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | البحث داخل قواعد AI الموجودة في قاعدة البيانات
        |--------------------------------------------------------------------------
        */

        $rule = $this->aiRuleService->findMatchingRule(
            problem: $problem,
            brandId: $brandId,
            carType: $carType,
            fuelType: $fuelType
        );

        if ($rule) {

            /*
            |--------------------------------------------------------------------------
            | محاولة معرفة الخدمة المرتبطة بالقاعدة
            |--------------------------------------------------------------------------
            |
            | ملاحظة:
            | AiRule حالياً لا يحتوي service_id.
            | لذلك البحث هنا يعتمد على اسم القاعدة فقط.
            |
            */

            $service = $this->recommendationService
                ->findService($rule->name);

            return [

                'source' => 'database_rule',

                'data' => [

                    'problem' => $problem,

                    'possible_causes' => [
                        $rule->name_ar
                    ],

                    'advice' => $rule->response_template,

                    'severity' => 'medium',

                    'service_name' => $service?->name ?? '',

                    'recommended_service' => $service
                        ? [
                            'id' => $service->id,
                            'name' => $service->name,
                            'name_ar' => $service->name_ar,
                            'price' => $service->base_price,
                            'duration_minutes' => $service->duration_minutes,
                        ]
                        : null,
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | لم توجد قاعدة
        | ننتقل إلى OpenRouter
        |--------------------------------------------------------------------------
        */

        $prompt = $this->buildPrompt($problem, $history);

        $content = $this->openRouterService
            ->ask($prompt);
        /*
        |--------------------------------------------------------------------------
        | تنظيف JSON
        |--------------------------------------------------------------------------
        */
        $content = str_replace(
            [
                '```json',
                '```',
            ],
            '',
            $content
        );

        $content = trim($content);

        /*
        |--------------------------------------------------------------------------
        | تحويل JSON إلى Array
        |--------------------------------------------------------------------------
        */

        $data = json_decode(
            $content,
            true
        );

        /*
        |--------------------------------------------------------------------------
        | حماية من JSON غير صحيح
        |--------------------------------------------------------------------------
        */

        if (!is_array($data)) {

            $data = [

                'problem' => $problem,

                'possible_causes' => [],

                'advice' => $content,

                'severity' => 'medium',

                'service_name' => '',

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | التأكد من وجود الحقول
        |--------------------------------------------------------------------------
        */

        $data['problem'] ??= $problem;

        $data['possible_causes'] ??= [];

        $data['advice'] ??= '';

        $data['severity'] ??= 'medium';

        $data['service_name'] ??= '';

        /*
        |--------------------------------------------------------------------------
        | التحقق من severity
        |--------------------------------------------------------------------------
        */

        if (!in_array(
            $data['severity'],
            ['low', 'medium', 'high'],
            true
        )) {

            $data['severity'] = 'medium';
        }

        /*
        |--------------------------------------------------------------------------
        | البحث عن الخدمة داخل قاعدة البيانات
        |--------------------------------------------------------------------------
        */

        $service = $this->recommendationService
            ->findService(
                $data['service_name']
            );

        /*
        |--------------------------------------------------------------------------
        | إضافة الخدمة المقترحة إلى نتيجة التشخيص
        |--------------------------------------------------------------------------
        */

        $data['recommended_service'] = $service
            ? [

                'id' => $service->id,

                'name' => $service->name,

                'name_ar' => $service->name_ar,

                'price' => $service->base_price,

                'duration_minutes' => $service->duration_minutes,

            ]
            : null;

        /*
        |--------------------------------------------------------------------------
        | النتيجة النهائية
        |--------------------------------------------------------------------------
        */

        return [

            'source' => 'openrouter',

            'data' => $data,

        ];
    }

    private function buildPrompt( string $problem,array $history): string
    {
        $prompt = <<<PROMPT
أنت مهندس ميكانيك وخبير محترف في تشخيص أعطال السيارات.

مهمتك:

- حلل المشكلة.
- استفد من جميع إجابات العميل.
- لا تخترع معلومات.
- إذا لم تكن متأكداً فاذكر أكثر الأسباب احتمالاً.
- أجب بالعربية.
- أعد JSON فقط.
- لا تضف Markdown.
- لا تضف أي نص خارج JSON.

إذا كانت المشكلة تتطلب خدمة موجودة داخل الورشة:

أعد اسم الخدمة بالإنجليزية تماماً كما هو موجود في قاعدة البيانات.


الخدمات المتوفرة حالياً:

Oil Change
Brake Inspection
Wheel Balancing
Battery Jump Start
Flat Tire Change
Exterior Wash
Interior Cleaning
إذا لم توجد خدمة مناسبة تماماً، أعد:

"service_name":""

مثال:

إذا كانت المشكلة:
السيارة تهتز عند سرعة 80

وكان السبب المحتمل يحتاج إلى ترصيص الإطارات:

"service_name":"Wheel Balancing"

إذا لم توجد خدمة مناسبة:

"service_name":""

المشكلة:

{$problem}

إجابات العميل:

PROMPT;

        foreach ($history as $item) {

            $question = $item['question'] ?? '';

            $answer = $item['answer'] ?? '';

            $prompt .= <<<TEXT

السؤال:
{$question}

الإجابة:
{$answer}

TEXT;
        }

        $prompt .= <<<JSON

أعد النتيجة بهذا الشكل فقط:

{
    "problem": "",
    "possible_causes": [],
    "advice": "",
    "severity": "medium",
    "service_name": ""
}

severity يجب أن تكون إحدى القيم التالية فقط:

low
medium
high

JSON;

        return $prompt;
    }
}
