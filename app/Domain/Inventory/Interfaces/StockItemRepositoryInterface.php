<?php

namespace App\Domain\Inventory\Interfaces;

use App\Domain\Inventory\Entities\StockItem;
use App\Domain\Inventory\ValueObjects\StockItemId;
use App\Domain\Inventory\ValueObjects\WarehouseId;
use App\Domain\Inventory\ValueObjects\CategoryId;
use App\Domain\Inventory\ValueObjects\SKU;

interface StockItemRepositoryInterface
{
    public function findById(StockItemId $id): ?StockItem;

    public function findBySku(SKU $sku): ?StockItem;

    public function findByWarehouseId(WarehouseId $warehouseId): array;

    public function findByCategoryId(CategoryId $categoryId): array;

    public function findByWarehouseAndCategory(WarehouseId $warehouseId, CategoryId $categoryId): array;

    public function findLowStock(): array;

    public function findOutOfStock(): array;

    public function findAll(): array;

    public function findActive(): array;

    public function save(StockItem $stockItem): void;

    public function delete(StockItemId $id): void;

    public function exists(StockItemId $id): bool;

    public function existsBySku(SKU $sku): bool;

    public function existsBySkuAndWarehouse(SKU $sku, WarehouseId $warehouseId): bool;
}
