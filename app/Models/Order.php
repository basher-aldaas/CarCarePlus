<?php

namespace App\Models;

use App\Enums\OrderEnums\OrderStatus;
use App\Enums\OrderEnums\OrderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'type' => OrderType::class,
        'status' => OrderStatus::class,
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id');
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function subServices(): HasMany
    {
        return $this->hasMany(OrderSubService::class, 'order_id');
    }
    public function materials(): HasMany
    {
        return $this->hasMany(OrderMaterial::class, 'order_id');
    }
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id');
    }
    public function roadAssistance(): HasOne
    {
        return $this->hasOne(RoadAssistanceDetail::class, 'order_id');
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id');
    }
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'order_id');
    }
    public function rating(): HasOne
    {
        return $this->hasOne(Rating::class, 'order_id');
    }
    public function reports(): HasMany
    {
        return $this->hasMany(EmployeeReport::class, 'order_id');
    }
    public function sparePartRequests(): HasMany
    {
        return $this->hasMany(SparePartRequest::class, 'order_id');
    }
    public function gpsLogs(): HasMany
    {
        return $this->hasMany(GpsLog::class, 'order_id');
    }
    public function schedulingConflictsAsFirst(): HasMany
    {
        return $this->hasMany(SchedulingConflict::class, 'order_id_1');
    }
    public function schedulingConflictsAsSecond(): HasMany
    {
        return $this->hasMany(SchedulingConflict::class, 'order_id_2');
    }

}
