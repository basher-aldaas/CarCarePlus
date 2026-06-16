<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $fillable = [
        'order_id',
        'customer_id',
        'employee_id',
        'service_rating',
        'employee_rating',
        'workshop_rating',
        'comment',
        'image_urls',
    ];

    protected $casts = [
        'service_rating' => 'integer',
        'employee_rating' => 'integer',
        'workshop_rating' => 'integer',
        'image_urls' => 'array',
    ];

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer() : BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function employee() : BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

}
