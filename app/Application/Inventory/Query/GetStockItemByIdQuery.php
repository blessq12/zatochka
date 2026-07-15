<?php

namespace App\Application\Inventory\Query;

final readonly class GetStockItemByIdQuery
{
    public function __construct(
        public int $stockItemId,
    ) {}
}
