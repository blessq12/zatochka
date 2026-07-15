<?php

namespace App\Application\Inventory\Query;

use App\Application\Inventory\DTO\StockItemDTO;
use App\Application\Inventory\ReadPort\StockReadPort;

final readonly class GetStockItemByIdHandler
{
    public function __construct(
        private StockReadPort $readPort,
    ) {}

    public function handle(GetStockItemByIdQuery $query): ?StockItemDTO
    {
        return $this->readPort->findById($query->stockItemId);
    }
}
