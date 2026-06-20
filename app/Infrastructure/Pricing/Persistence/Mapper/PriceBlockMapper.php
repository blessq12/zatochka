<?php

namespace App\Infrastructure\Pricing\Persistence\Mapper;

use App\Domain\Pricing\Entity\PriceBlock;
use App\Infrastructure\Pricing\Persistence\Eloquent\PriceBlockModel;

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
