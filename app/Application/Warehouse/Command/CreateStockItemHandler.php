<?php

namespace App\Application\Warehouse\Command;

use App\Application\Warehouse\Assembler\WarehouseResponseAssembler;
use App\Application\Warehouse\DTO\StockItemResponse;
use App\Domain\Warehouse\Aggregate\StockItem;
use App\Domain\Warehouse\Repository\StockItemRepository;
use App\Domain\Warehouse\StockItemCategory;

final readonly class CreateStockItemHandler
{
    public function __construct(
        private StockItemRepository $items,
        private WarehouseResponseAssembler $assembler,
    ) {}

    public function handle(
        string $category,
        string $name,
        string $unit,
        string $qtyOnHand = '0',
    ): StockItemResponse {
        $item = StockItem::create(
            StockItemCategory::from($category),
            $name,
            $unit,
            $qtyOnHand,
        );

        return $this->assembler->stockItem($this->items->save($item));
    }
}
