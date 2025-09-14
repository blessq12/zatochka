<?php

namespace App\Infrastructure\Warehouse\Mapper;

use App\Domain\Warehouse\Entity\StockCategory;
use App\Domain\Warehouse\Mapper\StockCategoryMapper;
use App\Models\StockCategory as StockCategoryModel;

class StockCategoryMapperImpl implements StockCategoryMapper
{
    public function toDomain($eloquentModel): StockCategory
    {
        return new StockCategory(
            id: $eloquentModel->id,
            warehouseId: $eloquentModel->warehouse_id,
            name: $eloquentModel->name,
            description: $eloquentModel->description,
            color: $eloquentModel->color,
            sortOrder: $eloquentModel->sort_order,
            isActive: $eloquentModel->is_active,
            isDeleted: $eloquentModel->is_deleted,
            createdAt: $eloquentModel->created_at ? $eloquentModel->created_at->toDateTime() : null,
            updatedAt: $eloquentModel->updated_at ? $eloquentModel->updated_at->toDateTime() : null,
        );
    }

    public function toEloquent(StockCategory $domainEntity): array
    {
        return [
            'warehouse_id' => $domainEntity->getWarehouseId(),
            'name' => $domainEntity->getName(),
            'description' => $domainEntity->getDescription(),
            'color' => $domainEntity->getColor(),
            'sort_order' => $domainEntity->getSortOrder(),
            'is_active' => $domainEntity->isActive(),
            'is_deleted' => $domainEntity->isDeleted(),
        ];
    }

    public function toEloquentModel(StockCategory $domainEntity): StockCategoryModel
    {
        $model = new StockCategoryModel();
        $model->fill($this->toEloquent($domainEntity));

        if ($domainEntity->getId()) {
            $model->id = $domainEntity->getId();
        }

        return $model;
    }
}
