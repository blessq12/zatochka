<?php

namespace App\Application\Pricing\QueryHandler;

use App\Application\Pricing\Query\GetPublicPriceListQuery;
use App\Domain\Pricing\Repository\PriceBlockRepositoryInterface;
use App\Domain\Pricing\Repository\PriceItemRepositoryInterface;

final class GetPublicPriceListQueryHandler
{
    public function __construct(
        private PriceBlockRepositoryInterface $priceBlocks,
        private PriceItemRepositoryInterface $priceItems,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function handle(GetPublicPriceListQuery $query): array
    {
        $prices = [];

        foreach ($this->priceBlocks->findAllOrdered() as $block) {
            $blockId = $block->id();
            if ($blockId === null) {
                continue;
            }

            $items = [];
            foreach ($this->priceItems->findByPriceBlockId($blockId) as $item) {
                $items[] = [
                    'name' => $item->name(),
                    'price' => $item->price(),
                    'description' => $item->description(),
                ];
            }

            $prices[] = [
                'type' => $block->type()->value,
                'title' => $block->title(),
                'items' => $items,
            ];
        }

        return $prices;
    }
}
