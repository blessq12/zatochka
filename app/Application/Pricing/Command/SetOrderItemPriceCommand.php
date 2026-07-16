<?php

namespace App\Application\Pricing\Command;

final readonly class SetOrderItemPriceCommand
{
    public function __construct(
        public int $orderItemId,
        public string $baseAmount,
        public string $currency = 'RUB',
    ) {}
}
