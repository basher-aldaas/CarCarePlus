<?php

namespace App\DTOs;

class RoadAssistanceDetailDTO
{
    public function __construct(
        public ?int $order_id,
        public ?int $problem_type_id,
        public ?string $car_type_size,
        public ?string $problem_description,
        public ?string $problem_image_url,
        public ?string $ai_diagnosis,
        public ?array $ai_chat_log,
        public ?bool $towing_required,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            order_id: isset($data['order_id']) ? (int) $data['order_id'] : null,
            problem_type_id: isset($data['problem_type_id']) ? (int) $data['problem_type_id'] : null,
            car_type_size: $data['car_type_size'] ?? null,
            problem_description: $data['problem_description'] ?? null,
            problem_image_url: $data['problem_image_url'] ?? null,
            ai_diagnosis: $data['ai_diagnosis'] ?? null,
            ai_chat_log: $data['ai_chat_log'] ?? null,
            towing_required: isset($data['towing_required']) ? (bool) $data['towing_required'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'order_id' => $this->order_id,
            'problem_type_id' => $this->problem_type_id,
            'car_type_size' => $this->car_type_size,
            'problem_description' => $this->problem_description,
            'problem_image_url' => $this->problem_image_url,
            'ai_diagnosis' => $this->ai_diagnosis,
            'ai_chat_log' => $this->ai_chat_log,
            'towing_required' => $this->towing_required,
        ], fn ($value) => $value !== null);
    }
}
