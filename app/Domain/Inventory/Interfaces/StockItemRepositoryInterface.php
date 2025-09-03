<?php

namespace App\Domain\Inventory\Interfaces;

use App\Domain\Inventory\Entities\StockItem;
// ... existing code ...
use App\Domain\Inventory\ValueObjects\SKU;

interface StockItemRepositoryInterface
{
    public function findById(int $id): ?StockItem;

    public function findBySku(SKU $sku): ?StockItem;

    public function findByWarehouseId(int $warehouseId): array;

    public function findByCategoryId(int $categoryId): array;

    public function findByWarehouseAndCategory(int $warehouseId, int $categoryId): array;

    public function findLowStock(): array;

    public function findOutOfStock(): array;

    public function findAll(): array;

    public function findActive(): array;

    public function save(StockItem $stockItem): void;

    public function delete(int $id): void;

    public function exists(int $id): bool;

    public function existsBySku(SKU $sku): bool;

    public function existsBySkuAndWarehouse(SKU $sku, int $warehouseId): bool;
}
