<?php

namespace App\DTOs\WorkshopsDTOs;

use App\DTOs\UserDTO;

class RegisterWorkshopDTO
{
    public function __construct(
        public readonly UserDTO $user,
        public readonly WorkshopDTO $workshop,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user: UserDTO::fromArray($data),

            workshop: WorkshopDTO::fromArray([
                'name' => $data['workshop_name'] ?? null,
                'name_ar' => $data['workshop_name_ar'] ?? null,
                'phone' => $data['workshop_phone'] ?? null,
                'address' => $data['workshop_address'] ?? null,
                'city' => $data['workshop_city'] ?? null,
                'latitude' => isset($data['latitude']) ? (float) $data['latitude'] : null,
                'longitude' => isset($data['longitude']) ? (float) $data['longitude'] : null,
            ]),
        );
    }

    public function toArray(): array
    {
        return [
            'user' => $this->user->toArray(),
            'workshop' => $this->workshop->toArray(),
        ];
    }
}
