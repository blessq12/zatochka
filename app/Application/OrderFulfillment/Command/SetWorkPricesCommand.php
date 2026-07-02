<?php

namespace App\Application\OrderFulfillment\Command;

final readonly class SetWorkPricesCommand
{
    /**
     * @param  array<int, string|null>  $pricesBySortOrder  ключ — sort_order; для заточки — цена за единицу инструмента
     */
    public function __construct(
        public int $orderId,
        public array $pricesBySortOrder,
    ) {}
}
