<?php

namespace App\Infrastructure\Persistence\Mappers\Catalog;

use App\Domain\Catalog\Entities\PriceBlock;
use App\Infrastructure\Persistence\Eloquent\Models\Catalog\PriceBlockModel;

final class PriceBlockMapper
{
    public function toDomain(PriceBlockModel $model): PriceBlock
    {
        return new PriceBlock(
            id: $model->id,
            type: $model->type,
            title: $model->title,
            sortOrder: $model->sort_order,
        );
    }

    public function fillModel(PriceBlock $block, PriceBlockModel $model): void
    {
        $model->fill([
            'type' => $block->type(),
            'title' => $block->title(),
            'sort_order' => $block->sortOrder(),
        ]);
    }
}
