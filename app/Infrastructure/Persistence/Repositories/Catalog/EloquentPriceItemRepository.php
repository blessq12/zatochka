<?php

namespace App\Infrastructure\Persistence\Repositories\Catalog;

use App\Domain\Catalog\Entities\PriceItem;
use App\Domain\Catalog\Repositories\PriceItemRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Catalog\PriceItemModel;
use App\Infrastructure\Persistence\Mappers\Catalog\PriceItemMapper;

final class EloquentPriceItemRepository implements PriceItemRepositoryInterface
{
    public function __construct(
        private PriceItemMapper $mapper,
    ) {}

    public function save(PriceItem $item): PriceItem
    {
        $model = $item->id() !== null
            ? PriceItemModel::query()->findOrFail($item->id())
            : new PriceItemModel;

        $this->mapper->fillModel($item, $model);
        $model->save();

        return $this->mapper->toDomain($model);
    }
}
