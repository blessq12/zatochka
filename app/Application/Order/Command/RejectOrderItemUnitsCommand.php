<?php

namespace App\Application\Order\Command;

final readonly class RejectOrderItemUnitsCommand
{
    public function __construct(
        public string $orderId,
        public int $orderItemId,
        public int $quantity,
        public string $reason,
    ) {}
}
