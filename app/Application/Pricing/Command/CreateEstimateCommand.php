<?php

namespace App\Application\Pricing\Command;

final readonly class CreateEstimateCommand
{
    public function __construct(
        public int $estimateId,
        public int $orderItemId,
        public string $estimatedAmount,
        public string $currency = 'RUB',
    ) {}
}
