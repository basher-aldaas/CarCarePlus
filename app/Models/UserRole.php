<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'user_roles';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'role_id',
    ];

    public function user()
    {

    }

//    public function customer() : BelongsTo
//    {
//        return $this->belongsTo(User::class, 'customer_id');
//    }

}
