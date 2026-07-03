<?php

namespace App\Application\OrderFulfillment\Command;

final readonly class RemoveWorkCommand
{
    public function __construct(
        public int $orderId,
        public int $masterId,
        public int $sortOrder,
    ) {}
}
