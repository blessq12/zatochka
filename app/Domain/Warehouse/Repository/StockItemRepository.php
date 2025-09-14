<?php

namespace App\Domain\Warehouse\Repository;

use App\Domain\Warehouse\Entity\StockItem;

interface StockItemRepository
{
    public function create(array $data): StockItem;

    public function get(int $id): ?StockItem;

    public function update(StockItem $item, array $data): StockItem;

    public function delete(int $id): bool;

    public function exists(int $id): bool;

    public function existsBySku(string $sku, ?int $excludeId = null): bool;

    public function getBySku(string $sku): ?StockItem;

    public function getByWarehouse(int $warehouseId): array;

    public function countByWarehouse(int $warehouseId): int;

    public function getByCategory(int $categoryId): array;

    public function getByWarehouseAndCategory(int $warehouseId, int $categoryId): array;

    public function getActive(): array;

    public function getLowStock(): array;

    public function getOutOfStock(): array;

    public function search(string $query): array;

    public function getAll(): array;
}
