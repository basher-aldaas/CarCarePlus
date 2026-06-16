<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'customer_id',
        'company_id',
        'car_id',
        'branch_id',
        'employee_id',
        'service_id',
        'type',
        'booking_type',
        'is_vip',
        'scheduled_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'cancel_reason',
        'location_lat',
        'location_lng',
        'location_address',
        'distance_km',
        'base_price',
        'vip_price',
        'distance_price',
        'sub_services_price',
        'order_material_price',
        'discount_amount',
        'total_price',
        'notes',
        'status',
        'assigned_at',
    ];

    protected $casts = [
        'is_vip' => 'boolean',
        'booking_type' => 'boolean',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'assigned_at' => 'datetime',
        'location_lat' => 'decimal:7',
        'location_lng' => 'decimal:7',
        'distance_km' => 'decimal:2',
        'base_price' => 'decimal:2',
        'vip_price' => 'decimal:2',
        'distance_price' => 'decimal:2',
        'sub_services_price' => 'decimal:2',
        'order_material_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function customer() : BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function car() : BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function branch() : BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee() : BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function service() : BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

}
