<?php

namespace App\Http\Resources;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request) : array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'image_url'  => $this->image_url,
            'is_active'  => (bool) $this->is_active,
            // سيظهر التوكن فقط إذا قمنا بتمريره إضافياً عند التسجيل أو الدخول
            'token'      => $this->when(isset($this->additional['token']), fn () => $this->additional['token']),
        ];
    }
}
