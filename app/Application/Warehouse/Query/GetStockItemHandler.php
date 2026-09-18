<?php

namespace App\Application\Warehouse\Query;

use App\Application\Warehouse\Assembler\WarehouseResponseAssembler;
use App\Application\Warehouse\DTO\StockItemResponse;
use App\Domain\Warehouse\Repository\StockItemRepository;

final readonly class GetStockItemHandler
{
    public function __construct(
        private StockItemRepository $items,
        private WarehouseResponseAssembler $assembler,
    ) {}

    public function handle(int $id): ?StockItemResponse
    {
        $item = $this->items->findById($id);

        return $item === null ? null : $this->assembler->stockItem($item);
    }
}
