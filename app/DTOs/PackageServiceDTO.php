<?php

namespace App\DTOs;

class PackageServiceDTO
{
    public function __construct(
        public ?int $package_id,
        public ?int $service_id,
        public ?int $allowed_count,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            package_id: isset($data['package_id']) ? (int) $data['package_id'] : null,
            service_id: isset($data['service_id']) ? (int) $data['service_id'] : null,
            allowed_count: isset($data['allowed_count']) ? (int) $data['allowed_count'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'package_id' => $this->package_id,
            'service_id' => $this->service_id,
            'allowed_count' => $this->allowed_count,
        ], fn ($value) => $value !== null);
    }
}
