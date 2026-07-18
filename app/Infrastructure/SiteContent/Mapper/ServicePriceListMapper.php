<?php

namespace App\Infrastructure\SiteContent\Mapper;

use App\Domain\SiteContent\Entity\PriceBlock;
use App\Domain\SiteContent\Entity\PriceItem;
use App\Domain\SiteContent\Entity\ServicePriceList;
use App\Domain\SiteContent\VO\PriceBlockType;
use App\Domain\SiteContent\VO\PricePrefix;
use App\Infrastructure\SiteContent\Model\PriceBlockModel;
use App\Infrastructure\SiteContent\Model\PriceItemModel;
use App\Shared\ValueObject\EntityId;
use Illuminate\Support\Collection;

final class ServicePriceListMapper
{
    /** @param Collection<int, PriceBlockModel> $models */
    public function toDomain(Collection $models): ServicePriceList
    {
        $blocks = $models
            ->sortBy('sort_order')
            ->values()
            ->map(function (PriceBlockModel $model): PriceBlock {
                $items = $model->items
                    ->sortBy('sort_order')
                    ->values()
                    ->map(static fn (PriceItemModel $item): PriceItem => PriceItem::reconstitute(
                        new EntityId((int) $item->id),
                        (string) $item->name,
                        (string) $item->price,
                        PricePrefix::fromNullable($item->prefix),
                        $item->description,
                        (int) $item->sort_order,
                    ))
                    ->all();

                return PriceBlock::reconstitute(
                    new EntityId((int) $model->id),
                    PriceBlockType::fromString((string) $model->type),
                    (string) $model->title,
                    $items,
                    (int) $model->sort_order,
                );
            })
            ->all();

        return ServicePriceList::reconstitute($blocks);
    }

    /**
     * @return array{
     *     blocks: list<array<string, mixed>>,
     *     items: list<array<string, mixed>>
     * }
     */
    public function toPersistence(ServicePriceList $priceList): array
    {
        $blocks = [];
        $items = [];

        foreach ($priceList->blocks() as $block) {
            $blocks[] = [
                'id' => $block->id()->value,
                'type' => $block->type()->value,
                'title' => $block->title(),
                'sort_order' => $block->sortOrder(),
            ];

            foreach ($block->items() as $item) {
                $items[] = [
                    'id' => $item->id()->value,
                    'price_block_id' => $block->id()->value,
                    'name' => $item->name(),
                    'price' => $item->price(),
                    'prefix' => $item->prefix()?->value,
                    'description' => $item->description(),
                    'sort_order' => $item->sortOrder(),
                ];
            }
        }

        return [
            'blocks' => $blocks,
            'items' => $items,
        ];
    }
}
