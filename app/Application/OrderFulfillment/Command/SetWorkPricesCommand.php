<?php

namespace App\Application\OrderFulfillment\Command;

final readonly class SetWorkPricesCommand
{
    /**
     * @param  array<int, string|null>  $pricesBySortOrder  ключ — sort_order; для заточки — цена за единицу
     * @param  array<string, string|null>  $pricesByToolType  ключ — tool_type; для заточки — цена за единицу по типу инструмента
     */
    public function __construct(
        public int $orderId,
        public array $pricesBySortOrder = [],
        public array $pricesByToolType = [],
    ) {}
}
