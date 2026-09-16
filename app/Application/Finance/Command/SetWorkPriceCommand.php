<?php

namespace App\Application\Finance\Command;

final readonly class SetWorkPriceCommand
{
    public function __construct(
        public int $performedWorkId,
        public string $baseAmount,
        public string $currency = 'RUB',
    ) {}
}
