<?php

namespace App\Infrastructure\Warehouse\Repository;

use App\Domain\Warehouse\Entity\Warehouse as WarehouseEntity;
use App\Domain\Warehouse\Mapper\WarehouseMapper;
use App\Domain\Warehouse\Repository\WarehouseRepository;
use App\Models\Warehouse;

class WarehouseRepositoryImpl implements WarehouseRepository
{
    public function __construct(
        private WarehouseMapper $warehouseMapper
    ) {}

    public function create(array $data): WarehouseEntity
    {
        $model = Warehouse::create($data);
        return $this->warehouseMapper->toDomain($model);
    }

    public function get(int $id): ?WarehouseEntity
    {
        $model = Warehouse::find($id);
        return $model ? $this->warehouseMapper->toDomain($model) : null;
    }

    public function update(WarehouseEntity $warehouse, array $data): WarehouseEntity
    {
        $model = Warehouse::find($warehouse->getId());
        $model->update($data);
        return $this->warehouseMapper->toDomain($model->fresh());
    }

    public function delete(int $id): bool
    {
        return Warehouse::where('id', $id)->update(['is_deleted' => true]) > 0;
    }

    public function exists(int $id): bool
    {
        return Warehouse::where('id', $id)->exists();
    }

    public function findByBranch(int $branchId): array
    {
        $models = Warehouse::where('branch_id', $branchId)
            ->where('is_deleted', false)
            ->get();

        return $models->map(fn($model) => $this->warehouseMapper->toDomain($model))->toArray();
    }

    public function findActiveByBranch(int $branchId): array
    {
        $models = Warehouse::where('branch_id', $branchId)
            ->where('is_deleted', false)
            ->where('is_active', true)
            ->get();

        return $models->map(fn($model) => $this->warehouseMapper->toDomain($model))->toArray();
    }

    public function existsByNameInBranch(string $name, int $branchId, ?int $excludeId = null): bool
    {
        $query = Warehouse::where('branch_id', $branchId)
            ->where('name', $name)
            ->where('is_deleted', false);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
