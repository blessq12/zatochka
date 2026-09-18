<?php

namespace App\Domain\Warehouse\Repository;

use App\Domain\Warehouse\Aggregate\StockItem;
use App\Domain\Warehouse\StockItemCategory;

interface StockItemRepository
{
    public function save(StockItem $item): StockItem;

    public function findById(int $id): ?StockItem;

    /**
     * @param  list<int>  $ids
     * @return list<StockItem>
     */
    public function findByIds(array $ids): array;

    /**
     * @return list<StockItem>
     */
    public function list(?StockItemCategory $category = null): array;
}
