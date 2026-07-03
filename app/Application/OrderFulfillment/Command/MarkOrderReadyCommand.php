<?php

namespace App\Application\OrderFulfillment\Command;

final readonly class MarkOrderReadyCommand
{
    public function __construct(
        public int $orderId,
        public int $masterId,
    ) {}
}
