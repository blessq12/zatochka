<?php

namespace App\Infrastructure\Warehouse\Persistence\Mapper;

use App\Domain\Warehouse\Entity\WarehouseItem;
use App\Domain\Warehouse\Enum\WarehouseItemType;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;

final class WarehouseItemMapper
{
    public function toDomain(WarehouseItemModel $model): WarehouseItem
    {
        return new WarehouseItem(
            id: $model->id,
            name: $model->name,
            sku: $model->sku,
            type: $model->type instanceof WarehouseItemType
                ? $model->type
                : WarehouseItemType::from($model->type),
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
            'type' => $item->type(),
            'quantity' => $item->quantity(),
            'unit' => $item->unit(),
            'price' => $item->price(),
        ]);
    }
}
