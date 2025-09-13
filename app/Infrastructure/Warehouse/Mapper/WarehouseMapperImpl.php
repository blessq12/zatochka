<?php

namespace App\Infrastructure\Warehouse\Mapper;

use App\Domain\Warehouse\Entity\Warehouse as WarehouseEntity;
use App\Domain\Warehouse\Mapper\WarehouseMapper;
use App\Models\Warehouse;

class WarehouseMapperImpl implements WarehouseMapper
{
    public function toDomain(Warehouse $model): WarehouseEntity
    {
        return new WarehouseEntity(
            id: $model->id,
            branchId: $model->branch_id,
            name: $model->name,
            description: $model->description,
            isActive: (bool) $model->is_active,
            isDeleted: (bool) $model->is_deleted,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }

    public function toEloquent(WarehouseEntity $entity): array
    {
        return [
            'id' => $entity->getId(),
            'branch_id' => $entity->getBranchId(),
            'name' => $entity->getName(),
            'description' => $entity->getDescription(),
            'is_active' => $entity->isActive(),
            'is_deleted' => $entity->isDeleted(),
            'created_at' => $entity->getCreatedAt(),
            'updated_at' => $entity->getUpdatedAt(),
        ];
    }

    public function fromArray(array $data): WarehouseEntity
    {
        return new WarehouseEntity(
            id: $data['id'] ?? null,
            branchId: $data['branch_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            isActive: $data['is_active'] ?? true,
            isDeleted: $data['is_deleted'] ?? false,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}
