<?php

namespace App\Application\Pricing\Command;

final readonly class SetOrderPricesCommand
{
    /**
     * @param list<array{order_item_id: int, base_amount: string}> $items
     */
    public function __construct(
        public string $orderId,
        public array $items,
        public string $currency = 'RUB',
    ) {}
}
