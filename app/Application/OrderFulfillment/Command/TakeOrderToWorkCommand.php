<?php

namespace App\Application\OrderFulfillment\Command;

final readonly class TakeOrderToWorkCommand
{
    public function __construct(
        public int $orderId,
        public int $masterId,
    ) {}
}
