<?php

namespace App\Domain\Inventory\Interfaces;

use App\Domain\Inventory\Entities\StockCategory;
use App\Domain\Inventory\ValueObjects\CategoryId;

interface StockCategoryRepositoryInterface
{
    public function findById(CategoryId $id): ?StockCategory;

    public function findByName(string $name): ?StockCategory;

    public function findAll(): array;

    public function findActive(): array;

    public function findBySortOrder(int $sortOrder): array;

    public function save(StockCategory $category): void;

    public function delete(CategoryId $id): void;

    public function exists(CategoryId $id): bool;

    public function existsByName(string $name): bool;
}
