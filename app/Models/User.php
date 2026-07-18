<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'image_url',
        'email_verified_at',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    /**
     * Send the password reset notification using our custom mailable.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function otpCodes(): HasMany
    {
        return $this->hasMany(OtpCode::class, 'user_id');
    }
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }
    public function points(): HasOne
    {
        return $this->hasOne(UserPoint::class, 'customer_id');
    }
    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'user_id');
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }
    public function userPackages(): HasMany
    {
        return $this->hasMany(UserPackage::class, 'user_id');
    }
    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'customer_id');
    }
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id');
    }
    public function managedBranch(): HasOne
    {
        return $this->hasOne(Branch::class, 'admin_id');
    }
    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class, 'super_admin_id');
    }
    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'created_by');
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id');
    }
    public function confirmedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'cash_confirmed_by');
    }
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }
    public function requestedMaterials(): HasMany
    {
        return $this->hasMany(OrderMaterial::class, 'requested_by');
    }
    public function orderStatusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'employee_id');
    }
    public function pointsTransactions(): HasMany
    {
        return $this->hasMany(PointsTransaction::class, 'customer_id');
    }
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'user_id');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'user_id');
    }
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class, 'customer_id');
    }
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
    public function approvedSpareParts(): HasMany
    {
        return $this->hasMany(SparePartRequest::class, 'admin_id');
    }
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'created_by');
    }


}
