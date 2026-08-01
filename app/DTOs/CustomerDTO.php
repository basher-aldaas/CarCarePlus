<?php

namespace App\DTOs;

class CustomerDTO
{
    public function __construct(
        public ?string $name,
        public ?string $email,
        public ?string $phone,
        public ?string $password,
        public ?bool $is_active,
        public ?string $image_url,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: array_key_exists('name', $data)
                ? $data['name']
                : null,

            email: array_key_exists('email', $data)
                ? $data['email']
                : null,

            phone: array_key_exists('phone', $data)
                ? $data['phone']
                : null,

            password: array_key_exists('password', $data)
                ? $data['password']
                : null,

            is_active: array_key_exists('is_active', $data)
                ? (bool) $data['is_active']
                : null,

            image_url: array_key_exists('image_url', $data)
                ? $data['image_url']
                : null,
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->email !== null) {
            $data['email'] = $this->email;
        }

        if ($this->phone !== null) {
            $data['phone'] = $this->phone;
        }

        if ($this->password !== null) {
            $data['password'] = $this->password;
        }

        if ($this->is_active !== null) {
            $data['is_active'] = $this->is_active;
        }

        if ($this->image_url !== null) {
            $data['image_url'] = $this->image_url;
        }

        return $data;
    }
}
