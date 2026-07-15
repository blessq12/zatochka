<?php

namespace App\Application\Order\DTO;

final readonly class CreateOrderItemDTO
{
    public function __construct(
        public int $orderItemId,
        public int $clientEquipmentId,
    ) {}
}
