<?php

namespace App\Application\Warehouse\Command;

use App\Application\Warehouse\Assembler\WarehouseResponseAssembler;
use App\Application\Warehouse\DTO\StockItemResponse;
use App\Domain\Warehouse\Repository\StockItemRepository;
use App\Domain\Warehouse\StockItemCategory;

final readonly class UpdateStockItemHandler
{
    public function __construct(
        private StockItemRepository $items,
        private WarehouseResponseAssembler $assembler,
    ) {}

    public function handle(
        int $id,
        string $category,
        string $name,
        string $unit,
    ): ?StockItemResponse {
        $item = $this->items->findById($id);
        if ($item === null) {
            return null;
        }

        $item->rename($name, $unit, StockItemCategory::from($category));

        return $this->assembler->stockItem($this->items->save($item));
    }
}
