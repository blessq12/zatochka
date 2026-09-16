<?php

namespace App\Application\Finance\Port;

final readonly class PerformedWorkRefDTO
{
    public function __construct(
        public int $id,
        public int $orderItemId,
        public string $orderId,
    ) {}
}
