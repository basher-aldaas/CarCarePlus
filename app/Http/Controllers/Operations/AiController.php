<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Services\Operations\AIConversationService;
use App\Services\Operations\AIDiagnosisService;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Service;

class AiController extends Controller
{
    protected $conversationService;
    protected $diagnosisService;

    public function __construct(
        AIConversationService $conversationService,
        AIDiagnosisService $diagnosisService
    ) {
        $this->diagnosisService = $diagnosisService;
        $this->conversationService = $conversationService;
    }

    public function diagnose(Request $request)
    {
        $request->validate([

            'problem'=>'required|string',

            'answers'=>'nullable|array',
            'answers.*.question' => 'required_with:answers','string',

            'answers.*.answer' =>   'required_with:answers','string',
            'order_id'=>'nullable|integer|exists:orders,id'

        ]);

        $history = $request->answers ?? [];

        /*
        |-----------------------------------------
        | إذا لم تنته الأسئلة بعد
        |-----------------------------------------
        */

        $nextQuestion = $this->conversationService
            ->nextQuestion($history);

        if ($nextQuestion !== null) {

            return response()->json([
                'success'  => true,
                'finished' => false,
                'question' => $nextQuestion
            ]);

        }

        /*
        |-----------------------------------------
        | انتهت الأسئلة
        |-----------------------------------------
        */



        $result = $this->diagnosisService->diagnose(
            orderId: $request->order_id,
            problem: $request->problem,
            history: $history
        );

        return response()->json([
            'success'  => true,
            'finished' => true,
            'source'   => $result['source'],
            'data'     => $result['data']
        ]);
    }

    public function applyRecommendedService(
        Request $request
    ) {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'service_id' => 'required|integer|exists:services,id',
        ]);

        $order = Order::findOrFail(
            $request->order_id
        );

        $service = Service::findOrFail(
            $request->service_id
        );

        $order->update([
            'service_id' => $service->id,
            'category_id' => $service->category_id,
            'total_price' => $service->base_price,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم ربط الخدمة بطلب الصيانة بنجاح.',
            'data' => [
                'order_id' => $order->id,
                'service' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'name_ar' => $service->name_ar,
                    'price' => $service->base_price,
                    'duration_minutes' => $service->duration_minutes,
                ],
                'total_price' => $order->total_price,
            ],
        ]);
    }
}
