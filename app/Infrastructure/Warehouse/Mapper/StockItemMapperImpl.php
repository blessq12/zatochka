<?php

namespace App\Infrastructure\Warehouse\Mapper;

use App\Domain\Warehouse\Entity\StockItem;
use App\Domain\Warehouse\Mapper\StockItemMapper;
use App\Models\StockItem as StockItemModel;

class StockItemMapperImpl implements StockItemMapper
{
    public function toDomain($eloquentModel): StockItem
    {
        return new StockItem(
            id: $eloquentModel->id,
            warehouseId: $eloquentModel->warehouse_id,
            categoryId: $eloquentModel->category_id,
            name: $eloquentModel->name,
            sku: $eloquentModel->sku,
            description: $eloquentModel->description,
            purchasePrice: $eloquentModel->purchase_price,
            retailPrice: $eloquentModel->retail_price,
            quantity: $eloquentModel->quantity,
            minStock: $eloquentModel->min_stock,
            unit: $eloquentModel->unit,
            supplier: $eloquentModel->supplier,
            manufacturer: $eloquentModel->manufacturer,
            model: $eloquentModel->model,
            isActive: $eloquentModel->is_active,
            isDeleted: $eloquentModel->is_deleted,
            createdAt: $eloquentModel->created_at ? $eloquentModel->created_at->toDateTime() : null,
            updatedAt: $eloquentModel->updated_at ? $eloquentModel->updated_at->toDateTime() : null,
        );
    }

    public function toEloquent(StockItem $domainEntity): array
    {
        return [
            'warehouse_id' => $domainEntity->getWarehouseId(),
            'category_id' => $domainEntity->getCategoryId(),
            'name' => $domainEntity->getName(),
            'sku' => $domainEntity->getSku(),
            'description' => $domainEntity->getDescription(),
            'purchase_price' => $domainEntity->getPurchasePrice(),
            'retail_price' => $domainEntity->getRetailPrice(),
            'quantity' => $domainEntity->getQuantity(),
            'min_stock' => $domainEntity->getMinStock(),
            'unit' => $domainEntity->getUnit(),
            'supplier' => $domainEntity->getSupplier(),
            'manufacturer' => $domainEntity->getManufacturer(),
            'model' => $domainEntity->getModel(),
            'is_active' => $domainEntity->isActive(),
            'is_deleted' => $domainEntity->isDeleted(),
        ];
    }

    public function toEloquentModel(StockItem $domainEntity): StockItemModel
    {
        $model = new StockItemModel();
        $model->fill($this->toEloquent($domainEntity));

        if ($domainEntity->getId()) {
            $model->id = $domainEntity->getId();
        }

        return $model;
    }
}
