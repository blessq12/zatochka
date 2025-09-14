<?php

namespace App\Infrastructure\Warehouse\Repository;

use App\Domain\Warehouse\Entity\StockItem;
use App\Domain\Warehouse\Repository\StockItemRepository;
use App\Domain\Warehouse\Mapper\StockItemMapper;
use App\Models\StockItem as StockItemModel;

class StockItemRepositoryImpl implements StockItemRepository
{
    public function __construct(
        private StockItemMapper $mapper
    ) {}

    public function create(array $data): StockItem
    {
        $model = StockItemModel::create($data);
        return $this->mapper->toDomain($model);
    }

    public function get(int $id): ?StockItem
    {
        $model = StockItemModel::find($id);
        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function update(StockItem $item, array $data): StockItem
    {
        $model = StockItemModel::findOrFail($item->getId());
        $model->update($data);
        return $this->mapper->toDomain($model);
    }

    public function delete(int $id): bool
    {
        $model = StockItemModel::findOrFail($id);
        return $model->update(['is_deleted' => true]);
    }

    public function exists(int $id): bool
    {
        return StockItemModel::where('id', $id)->exists();
    }

    public function existsBySku(string $sku, ?int $excludeId = null): bool
    {
        $query = StockItemModel::where('sku', $sku);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getBySku(string $sku): ?StockItem
    {
        $model = StockItemModel::where('sku', $sku)->first();
        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function getByWarehouse(int $warehouseId): array
    {
        return StockItemModel::where('warehouse_id', $warehouseId)
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function countByWarehouse(int $warehouseId): int
    {
        return StockItemModel::where('warehouse_id', $warehouseId)
            ->where('is_deleted', false)
            ->count();
    }

    public function getByCategory(int $categoryId): array
    {
        return StockItemModel::where('category_id', $categoryId)
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function getByWarehouseAndCategory(int $warehouseId, int $categoryId): array
    {
        return StockItemModel::where('warehouse_id', $warehouseId)
            ->where('category_id', $categoryId)
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function getActive(): array
    {
        return StockItemModel::where('is_deleted', false)
            ->where('is_active', true)
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function getLowStock(): array
    {
        return StockItemModel::where('is_deleted', false)
            ->where('is_active', true)
            ->whereRaw('quantity <= min_stock')
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function getOutOfStock(): array
    {
        return StockItemModel::where('is_deleted', false)
            ->where('is_active', true)
            ->where('quantity', '<=', 0)
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function search(string $query): array
    {
        return StockItemModel::where('is_deleted', false)
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('sku', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function getAll(): array
    {
        return StockItemModel::all()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }
}
