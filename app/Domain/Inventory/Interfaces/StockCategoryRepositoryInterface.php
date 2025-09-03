<?php

namespace App\Domain\Inventory\Interfaces;

use App\Domain\Inventory\Entities\StockCategory;
// ... existing code ...

interface StockCategoryRepositoryInterface
{
    public function findById(int $id): ?StockCategory;

    public function findByName(string $name): ?StockCategory;

    public function findAll(): array;

    public function findActive(): array;

    public function findBySortOrder(int $sortOrder): array;

    public function save(StockCategory $category): void;

    public function delete(int $id): void;

    public function exists(int $id): bool;

    public function existsByName(string $name): bool;
}
