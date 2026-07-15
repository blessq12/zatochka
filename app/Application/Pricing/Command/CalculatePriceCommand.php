<?php

namespace App\Application\Pricing\Command;

final readonly class CalculatePriceCommand
{
    public function __construct(
        public int $estimateId,
        public int $itemPriceId,
        public string $baseAmount,
        public string $currency = 'RUB',
    ) {}
}
