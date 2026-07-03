<?php

namespace App\Application\OrderFulfillment\Command;

final readonly class CancelOrderCommand
{
    public function __construct(
        public int $orderId,
    ) {}
}
