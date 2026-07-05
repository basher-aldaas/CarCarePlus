<?php

namespace App\DTOs;

class UserDTO
{
    public function __construct(
        public ?string $name, //اشارة الاستفهام هنا ممكن ان يكون نص سترينغ او ممكن ان يكون فارغ نول
        public ?string $email,
        public ?string $phone,
        public ?string $password,
        public ?bool $is_active,
        public ?string $image_url,
    )
    {}

    public static function fromArray(array $data) : self
    {
        return new self(
            name:        $data['name']  ?? null, //اذا كان يوجد اسم فحطه هون والا حط نول
            email:       $data['email'] ?? null,
            phone:       $data['phone'] ?? null,
            password:    $data['password'] ?? null,
            is_active:   isset($data['is_active']) ? (bool) $data['is_active'] : null,
            image_url:   $data['image_url'] ?? null,
        );
    }

    public function toArray() : array
    {
        return array_filter([
            'name'  => $this->name,
            'email'  => $this->email,
            'phone'  => $this->phone,
            'password'  => $this->password,
            'is_active'  => $this->is_active,
            'image_url'  => $this->image_url,
        ], fn ($value) => $value !== null);
    }
}
