<?php

namespace App\Domain\Warehouse\Repository;

use App\Domain\Warehouse\Entity\StockCategory;

interface StockCategoryRepository
{
    public function create(array $data): StockCategory;

    public function get(int $id): ?StockCategory;

    public function update(StockCategory $category, array $data): StockCategory;

    public function delete(int $id): bool;

    public function exists(int $id): bool;

    public function existsByName(string $name, ?int $excludeId = null): bool;

    public function existsByNameInWarehouse(string $name, int $warehouseId, ?int $excludeId = null): bool;

    public function countByWarehouse(int $warehouseId): int;

    public function getAll(): array;

    public function getActive(): array;

    public function getOrdered(): array;

    public function getActiveOrdered(): array;
}
