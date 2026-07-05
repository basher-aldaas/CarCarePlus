<?php

namespace App\DTOs\AuthDTOs;

class ResetPasswordDTO
{
    public function __construct(
        public string $email,
        public string $token,
        public string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email:    $data['email'],
            token:    $data['token'],
            password: $data['password'],
        );
    }

    /**
     * Credentials array expected by Laravel's password broker.
     *
     * @return array{email: string, token: string, password: string}
     */
    public function toCredentials(): array
    {
        return [
            'email'    => $this->email,
            'token'    => $this->token,
            'password' => $this->password,
        ];
    }
}
