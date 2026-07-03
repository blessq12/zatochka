<?php

namespace App\Domain\OrderFulfillment\Event;

use App\Domain\OrderFulfillment\Entity\Order;

final readonly class EquipmentLinkedToOrder
{
    public function __construct(
        public Order $order,
        public int $equipmentId,
    ) {}
}
