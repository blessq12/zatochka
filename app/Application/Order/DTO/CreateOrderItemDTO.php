<?php

namespace App\Application\Order\DTO;

final readonly class CreateOrderItemDTO
{
    public function __construct(
        public ?int $orderItemId = null,
        public ?int $clientEquipmentId = null,
        public ?string $toolName = null,
        public ?string $toolType = null,
        public ?int $quantity = null,
    ) {}
}
