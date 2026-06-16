<?php

namespace App\Infrastructure\Catalog\Persistence\Repository;

use App\Domain\Catalog\Entity\PriceItem;
use App\Domain\Catalog\Repository\PriceItemRepositoryInterface;
use App\Infrastructure\Catalog\Persistence\Eloquent\PriceItemModel;
use App\Infrastructure\Catalog\Persistence\Mapper\PriceItemMapper;

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
