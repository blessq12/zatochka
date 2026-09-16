<?php

namespace App\Application\Finance\Command;

final readonly class ClearWorkPricesForOrderCommand
{
    public function __construct(
        public string $orderId,
    ) {}
}
