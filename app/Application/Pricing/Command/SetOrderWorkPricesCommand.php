<?php

namespace App\Application\Pricing\Command;

final readonly class SetOrderWorkPricesCommand
{
    /**
     * @param list<array{performed_work_id: int, base_amount: string}> $works
     */
    public function __construct(
        public string $orderId,
        public array $works,
        public string $currency = 'RUB',
    ) {}
}
