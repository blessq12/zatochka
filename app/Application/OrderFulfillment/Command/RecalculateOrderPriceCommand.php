<?php

namespace App\Application\OrderFulfillment\Command;

final readonly class RecalculateOrderPriceCommand
{
    public function __construct(
        public int $orderId,
    ) {}
}
