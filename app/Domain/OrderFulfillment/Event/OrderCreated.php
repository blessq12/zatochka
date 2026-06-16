<?php

namespace App\Domain\OrderFulfillment\Event;

use App\Domain\OrderFulfillment\Entity\Order;

final readonly class OrderCreated
{
    public function __construct(
        public Order $order,
    ) {}
}
