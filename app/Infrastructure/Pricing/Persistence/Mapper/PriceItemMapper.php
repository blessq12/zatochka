<?php

namespace App\Infrastructure\Pricing\Persistence\Mapper;

use App\Domain\Pricing\Entity\PriceItem;
use App\Infrastructure\Pricing\Persistence\Eloquent\PriceItemModel;

final class PriceItemMapper
{
    public function toDomain(PriceItemModel $model): PriceItem
    {
        return new PriceItem(
            id: $model->id,
            priceBlockId: $model->price_block_id,
            name: $model->name,
            price: (string) $model->price,
            pricePrefix: $model->price_prefix,
            description: $model->description,
            sortOrder: $model->sort_order,
        );
    }

    public function fillModel(PriceItem $item, PriceItemModel $model): void
    {
        $model->fill([
            'price_block_id' => $item->priceBlockId(),
            'name' => $item->name(),
            'price' => $item->price(),
            'price_prefix' => $item->pricePrefix(),
            'description' => $item->description(),
            'sort_order' => $item->sortOrder(),
        ]);
    }
}
