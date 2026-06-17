<?php

namespace App\Models;

use App\Enums\CarEnums\CarTypeSize;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoadAssistanceDetail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'problem_type_id',
        'car_type_size',
        'problem_description',
        'problem_image_url',
        'ai_diagnosis',
        'ai_chat_log',
        'towing_required',
    ];

    protected $casts = [
        'car_type' => CarTypeSize::class,
        'ai_chat_log' => 'array',
        'towing_required' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function problemType(): BelongsTo
    {
        return $this->belongsTo(ProblemType::class, 'problem_type_id');
    }

}
