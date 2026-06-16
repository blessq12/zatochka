<?php

namespace App\Application\OrderFulfillment\Command;

final readonly class RemoveMaterialFromOrderCommand
{
    public function __construct(
        public int $orderId,
        public int $materialId,
    ) {}
}
