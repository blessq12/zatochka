<?php

namespace App\Domain\OrderFulfillment\Event;

use App\Domain\OrderFulfillment\Entity\Order;

final readonly class OrderReturnedForRework
{
    public function __construct(
        public Order $order,
    ) {}
}
