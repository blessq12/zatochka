<?php

namespace App\Application\Order\Command;

final readonly class MarkOrderInProgressCommand
{
    public function __construct(
        public string $orderId,
    ) {}
}
