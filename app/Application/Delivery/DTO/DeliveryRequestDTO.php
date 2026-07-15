<?php

namespace App\Application\Delivery\DTO;

final readonly class DeliveryRequestDTO
{
    public function __construct(
        public int $id,
        public int $orderId,
        public string $status,
        public bool $pickup,
        public string $city,
        public string $street,
        public string $building,
        public ?string $apartment,
        public ?int $courierId,
    ) {}
}
