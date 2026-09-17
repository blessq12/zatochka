<?php

namespace App\Infrastructure\Warehouse\Repository;

use App\Domain\Warehouse\Aggregate\StockItem;
use App\Domain\Warehouse\Repository\StockItemRepository;
use App\Domain\Warehouse\StockItemCategory;
use App\Infrastructure\Warehouse\Eloquent\StockItemModel;

final class EloquentStockItemRepository implements StockItemRepository
{
    public function save(StockItem $item): StockItem
    {
        /** @var StockItemModel $model */
        $model = $item->id() === null
            ? new StockItemModel()
            : StockItemModel::query()->findOrFail($item->id());

        $model->category = $item->category()->value;
        $model->name = $item->name();
        $model->qty_on_hand = $item->qtyOnHand();
        $model->unit = $item->unit();
        $model->save();

        if ($item->id() === null) {
            $item->assignId((int) $model->id);
        }

        return $item;
    }

    public function findById(int $id): ?StockItem
    {
        /** @var StockItemModel|null $model */
        $model = StockItemModel::query()->find($id);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $models = StockItemModel::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $items = [];
        foreach ($ids as $id) {
            $model = $models->get($id);
            if ($model !== null) {
                $items[] = $this->toDomain($model);
            }
        }

        return $items;
    }

    public function list(?StockItemCategory $category = null): array
    {
        $query = StockItemModel::query()->orderBy('name');
        if ($category !== null) {
            $query->where('category', $category->value);
        }

        return $query->get()
            ->map(fn (StockItemModel $model): StockItem => $this->toDomain($model))
            ->all();
    }

    private function toDomain(StockItemModel $model): StockItem
    {
        return new StockItem(
            (int) $model->id,
            StockItemCategory::from((string) $model->category),
            (string) $model->name,
            (string) $model->qty_on_hand,
            (string) $model->unit,
        );
    }
}
