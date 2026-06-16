<?php

namespace App\Infrastructure\Catalog\Persistence\Repository;

use App\Domain\Catalog\Entity\PriceBlock;
use App\Domain\Catalog\Repository\PriceBlockRepositoryInterface;
use App\Infrastructure\Catalog\Persistence\Eloquent\PriceBlockModel;
use App\Infrastructure\Catalog\Persistence\Mapper\PriceBlockMapper;

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
}
