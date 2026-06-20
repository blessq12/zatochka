<?php

namespace App\Infrastructure\Pricing\Persistence\Repository;

use App\Domain\Pricing\Entity\PriceBlock;
use App\Domain\Pricing\Repository\PriceBlockRepositoryInterface;
use App\Infrastructure\Pricing\Persistence\Eloquent\PriceBlockModel;
use App\Infrastructure\Pricing\Persistence\Mapper\PriceBlockMapper;

final class EloquentPriceBlockRepository implements PriceBlockRepositoryInterface
{
    public function __construct(
        private PriceBlockMapper $mapper,
    ) {}

    public function findById(int $id): ?PriceBlock
    {
        $model = PriceBlockModel::query()->find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(PriceBlock $block): PriceBlock
    {
        $model = $block->id() !== null
            ? PriceBlockModel::query()->findOrFail($block->id())
            : new PriceBlockModel;

        $this->mapper->fillModel($block, $model);
        $model->save();

        return $this->mapper->toDomain($model);
    }

    public function findAllOrdered(): array
    {
        return PriceBlockModel::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PriceBlockModel $model) => $this->mapper->toDomain($model))
            ->all();
    }
}
