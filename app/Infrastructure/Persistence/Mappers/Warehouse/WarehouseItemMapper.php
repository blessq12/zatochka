<?php

namespace App\Infrastructure\Persistence\Mappers\Warehouse;

use App\Domain\Warehouse\Entities\WarehouseItem;
use App\Infrastructure\Persistence\Eloquent\Models\Warehouse\WarehouseItemModel;

final class WarehouseItemMapper
{
    public function toDomain(WarehouseItemModel $model): WarehouseItem
    {
        return new WarehouseItem(
            id: $model->id,
            name: $model->name,
            sku: $model->sku,
            categoryName: $model->category_name,
            quantity: (string) $model->quantity,
            unit: $model->unit,
            price: (string) $model->price,
        );
    }

    public function fillModel(WarehouseItem $item, WarehouseItemModel $model): void
    {
        $model->fill([
            'name' => $item->name(),
            'sku' => $item->sku(),
            'category_name' => $item->categoryName(),
            'quantity' => $item->quantity(),
            'unit' => $item->unit(),
            'price' => $item->price(),
        ]);
    }
}
