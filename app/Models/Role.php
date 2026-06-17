<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{

    const ROLE_SUPER_ADMIN = 1;

    const ROLE_ADMIN = 2;

    const ROLE_CUSTOMER = 3;

    const ROLE_COMPANY_OWNER = 4;

    const ROLE_TECHNICIAN = 5;

    const ROLE_WASHER = 6;

    const ROLE_WORKSHOP = 7;
    protected $fillable  = [
        'name',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id');
    }
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }


}
