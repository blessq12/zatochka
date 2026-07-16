<?php

namespace App\Application\Pricing\Command;

final readonly class ClearWorkPricesForOrderCommand
{
    public function __construct(
        public string $orderId,
    ) {}
}
