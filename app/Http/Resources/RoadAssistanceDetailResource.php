<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoadAssistanceDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'problem_type_id' => $this->problem_type_id,
            'problem_type' => new ProblemTypeResource($this->whenLoaded('problemType')),
            'car_type_size' => $this->car_type_size?->value,
            'problem_description' => $this->problem_description,
            'problem_image_url' => $this->problem_image_url,
            'ai_diagnosis' => $this->ai_diagnosis,
            'ai_chat_log' => $this->ai_chat_log ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}