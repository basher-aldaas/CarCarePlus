<?php


namespace App\DTOs;

class ApplyOrderServiceDTO
{
    public function __construct(
        public int $order_id,
        public int $service_id,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            order_id: (int)$data['order_id'],
            service_id: (int)$data['service_id'],
        );
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->order_id,
            'service_id' => $this->service_id,
        ];
    }
}
