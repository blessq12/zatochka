<?php

namespace App\Application\Order\Command;

final readonly class CloseOrderCommand
{
    public function __construct(
        public string $orderId,
    ) {}
}
