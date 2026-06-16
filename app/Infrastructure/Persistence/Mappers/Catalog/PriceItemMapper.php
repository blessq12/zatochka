<?php

namespace App\Infrastructure\Persistence\Mappers\Catalog;

use App\Domain\Catalog\Entities\PriceItem;
use App\Infrastructure\Persistence\Eloquent\Models\Catalog\PriceItemModel;

final class PriceItemMapper
{
    public function toDomain(PriceItemModel $model): PriceItem
    {
        return new PriceItem(
            id: $model->id,
            priceBlockId: $model->price_block_id,
            name: $model->name,
            price: (string) $model->price,
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
            'description' => $item->description(),
            'sort_order' => $item->sortOrder(),
        ]);
    }
}
