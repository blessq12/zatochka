<?php

namespace App\Domain\Inventory\Repository;

use App\Domain\Inventory\Entity\StockItem;
use App\Shared\ValueObject\EntityId;

interface StockItemRepository
{
    public function save(StockItem $stockItem): void;

    public function findById(EntityId $id): ?StockItem;

    public function getById(EntityId $id): StockItem;

    public function findByMaterialId(EntityId $materialId): ?StockItem;
}
