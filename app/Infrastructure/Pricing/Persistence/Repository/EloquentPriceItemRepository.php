<?php

namespace App\Infrastructure\Pricing\Persistence\Repository;

use App\Domain\Pricing\Entity\PriceItem;
use App\Domain\Pricing\Repository\PriceItemRepositoryInterface;
use App\Infrastructure\Pricing\Persistence\Eloquent\PriceItemModel;
use App\Infrastructure\Pricing\Persistence\Mapper\PriceItemMapper;

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

    public function findByPriceBlockId(int $priceBlockId): array
    {
        return PriceItemModel::query()
            ->where('price_block_id', $priceBlockId)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PriceItemModel $model) => $this->mapper->toDomain($model))
            ->all();
    }
}
