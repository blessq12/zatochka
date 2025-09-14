<?php

namespace App\Infrastructure\Warehouse\Repository;

use App\Domain\Warehouse\Entity\StockCategory;
use App\Domain\Warehouse\Repository\StockCategoryRepository;
use App\Domain\Warehouse\Mapper\StockCategoryMapper;
use App\Models\StockCategory as StockCategoryModel;

class StockCategoryRepositoryImpl implements StockCategoryRepository
{
    public function __construct(
        private StockCategoryMapper $mapper
    ) {}

    public function create(array $data): StockCategory
    {
        $model = StockCategoryModel::create($data);
        return $this->mapper->toDomain($model);
    }

    public function get(int $id): ?StockCategory
    {
        $model = StockCategoryModel::find($id);
        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function update(StockCategory $category, array $data): StockCategory
    {
        $model = StockCategoryModel::findOrFail($category->getId());
        $model->update($data);
        return $this->mapper->toDomain($model);
    }

    public function delete(int $id): bool
    {
        $deleted = StockCategoryModel::where('id', $id)->delete();
        return $deleted > 0;
    }

    public function exists(int $id): bool
    {
        return StockCategoryModel::where('id', $id)->exists();
    }

    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        $query = StockCategoryModel::where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function existsByNameInWarehouse(string $name, int $warehouseId, ?int $excludeId = null): bool
    {
        $query = StockCategoryModel::where('name', $name)
            ->where('warehouse_id', $warehouseId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function countByWarehouse(int $warehouseId): int
    {
        return StockCategoryModel::where('warehouse_id', $warehouseId)
            ->where('is_deleted', false)
            ->count();
    }

    public function getAll(): array
    {
        return StockCategoryModel::all()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function getActive(): array
    {
        return StockCategoryModel::where('is_deleted', false)
            ->where('is_active', true)
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function getOrdered(): array
    {
        return StockCategoryModel::orderBy('sort_order', 'asc')
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function getActiveOrdered(): array
    {
        return StockCategoryModel::where('is_deleted', false)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }
}
