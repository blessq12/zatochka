<?php

namespace App\Infrastructure\Warehouse\Persistence\Repository;

use App\Domain\Warehouse\Entity\WarehouseItem;
use App\Domain\Warehouse\Repository\WarehouseItemRepositoryInterface;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;
use App\Infrastructure\Warehouse\Persistence\Mapper\WarehouseItemMapper;

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

    public function search(?string $query, int $page, int $perPage): array
    {
        $builder = WarehouseItemModel::query();

        if ($query !== null && $query !== '') {
            $builder->where(function ($q) use ($query): void {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            });
        }

        $builder->orderBy('name');

        $total = (clone $builder)->count();
        $models = $builder->forPage($page, $perPage)->get();

        return [
            'items' => $models->map(fn (WarehouseItemModel $model) => $this->mapper->toDomain($model))->all(),
            'total' => $total,
        ];
    }
}
