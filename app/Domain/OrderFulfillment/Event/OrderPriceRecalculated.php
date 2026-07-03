<?php

namespace App\Domain\OrderFulfillment\Event;

use App\Domain\OrderFulfillment\Entity\Order;

final readonly class OrderPriceRecalculated
{
    public function __construct(
        public Order $order,
    ) {}
}
