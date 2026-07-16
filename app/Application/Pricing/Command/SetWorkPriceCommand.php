<?php

namespace App\Application\Pricing\Command;

final readonly class SetWorkPriceCommand
{
    public function __construct(
        public int $masterCommentId,
        public string $baseAmount,
        public string $currency = 'RUB',
    ) {}
}
