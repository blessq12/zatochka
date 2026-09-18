<?php

namespace App\Application\Warehouse\Command;

use App\Application\Warehouse\Assembler\WarehouseResponseAssembler;
use App\Application\Warehouse\DTO\StockItemResponse;
use App\Domain\Warehouse\Repository\StockItemRepository;

final readonly class ReceiveStockItemHandler
{
    public function __construct(
        private StockItemRepository $items,
        private WarehouseResponseAssembler $assembler,
    ) {}

    public function handle(int $id, string $qty): ?StockItemResponse
    {
        $item = $this->items->findById($id);
        if ($item === null) {
            return null;
        }

        $item->receive($qty);

        return $this->assembler->stockItem($this->items->save($item));
    }
}
