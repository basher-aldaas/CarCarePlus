<?php

namespace App\DTOs\WorkshopsDTOs;

use App\DTOs\UserDTO;
use App\DTOs\WorkshopsDTOs\WorkshopDTO;

class CreateWorkshopUserDTO
{
    public function __construct(
        public readonly UserDTO $userDto,
        public readonly WorkshopDTO $workshopDto
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userDto: UserDTO::fromArray($data),

            workshopDto: WorkshopDTO::fromArray([
                'name' => $data['workshop_name'] ?? null,
                'name_ar' => $data['workshop_name_ar'] ?? null,
                'phone' => $data['workshop_phone'] ?? null,
                'address' => $data['workshop_address'] ?? null,
                'city' => $data['workshop_city'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'status' => $data['status'] ?? true,
                'rating_avg' => $data['rating_avg'] ?? null,
            ]),
        );
    }

    public function toArray(): array
    {
        return [
            'user' => $this->userDto->toArray(),
            'workshop' => $this->workshopDto->toArray(),
        ];
    }
}
