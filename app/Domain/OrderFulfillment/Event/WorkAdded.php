<?php

namespace App\Domain\OrderFulfillment\Event;

use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Entity\OrderWork;

final readonly class WorkAdded
{
    public function __construct(
        public Order $order,
        public OrderWork $work,
    ) {}
}
