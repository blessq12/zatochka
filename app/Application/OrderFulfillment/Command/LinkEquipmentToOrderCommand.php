<?php

namespace App\Application\OrderFulfillment\Command;

final readonly class LinkEquipmentToOrderCommand
{
    public function __construct(
        public int $orderId,
        public int $equipmentId,
    ) {}
}
