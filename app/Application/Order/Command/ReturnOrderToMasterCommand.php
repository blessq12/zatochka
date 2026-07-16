<?php

namespace App\Application\Order\Command;

final readonly class ReturnOrderToMasterCommand
{
    public function __construct(
        public string $orderId,
        public string $reason,
    ) {}
}
