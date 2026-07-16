<?php

namespace App\Application\Order\Command;

final readonly class CancelOrderCommand
{
    public function __construct(
        public string $orderId,
        public string $reason,
    ) {}
}
