<?php

namespace App\Application\Warehouse\Query;

use App\Application\Warehouse\Assembler\WarehouseResponseAssembler;
use App\Application\Warehouse\DTO\StockItemResponse;
use App\Domain\Warehouse\Repository\StockItemRepository;
use App\Domain\Warehouse\StockItemCategory;

final readonly class ListStockItemsHandler
{
    public function __construct(
        private StockItemRepository $items,
        private WarehouseResponseAssembler $assembler,
    ) {}

    /**
     * @return list<StockItemResponse>
     */
    public function handle(?string $category = null): array
    {
        $cat = $category !== null && $category !== ''
            ? StockItemCategory::from($category)
            : null;

        return array_map(
            fn ($item) => $this->assembler->stockItem($item),
            $this->items->list($cat),
        );
    }
}
