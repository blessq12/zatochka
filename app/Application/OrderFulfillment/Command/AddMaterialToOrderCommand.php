<?php

namespace App\Application\OrderFulfillment\Command;

final readonly class AddMaterialToOrderCommand
{
    public function __construct(
        public int $orderId,
        public int $warehouseItemId,
        public string $quantity,
    ) {}
}
