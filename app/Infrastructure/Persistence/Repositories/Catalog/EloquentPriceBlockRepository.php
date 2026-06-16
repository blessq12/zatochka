<?php

namespace App\Infrastructure\Persistence\Repositories\Catalog;

use App\Domain\Catalog\Entities\PriceBlock;
use App\Domain\Catalog\Repositories\PriceBlockRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Catalog\PriceBlockModel;
use App\Infrastructure\Persistence\Mappers\Catalog\PriceBlockMapper;

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
