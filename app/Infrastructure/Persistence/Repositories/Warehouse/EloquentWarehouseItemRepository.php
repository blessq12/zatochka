<?php

namespace App\Infrastructure\Persistence\Repositories\Warehouse;

use App\Domain\Warehouse\Entities\WarehouseItem;
use App\Domain\Warehouse\Repositories\WarehouseItemRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Warehouse\WarehouseItemModel;
use App\Infrastructure\Persistence\Mappers\Warehouse\WarehouseItemMapper;

final class EloquentWarehouseItemRepository implements WarehouseItemRepositoryInterface
{
    public function __construct(
        private WarehouseItemMapper $mapper,
    ) {}

    public function findById(int $id): ?WarehouseItem
    {
        $model = WarehouseItemModel::query()->find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(WarehouseItem $item): WarehouseItem
    {
        $model = $item->id() !== null
            ? WarehouseItemModel::query()->findOrFail($item->id())
            : new WarehouseItemModel;

        $this->mapper->fillModel($item, $model);
        $model->save();

        return $this->mapper->toDomain($model);
    }
}
