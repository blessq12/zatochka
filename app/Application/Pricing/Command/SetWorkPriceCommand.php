<?php

namespace App\Application\Pricing\Command;

final readonly class SetWorkPriceCommand
{
    public function __construct(
        public int $performedWorkId,
        public string $baseAmount,
        public string $currency = 'RUB',
    ) {}
}
