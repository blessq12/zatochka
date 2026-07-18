<?php

namespace App\Application\SiteContent\Command;

use App\Application\Shared\EntityIdGenerator;
use App\Domain\SiteContent\Entity\PriceBlock;
use App\Domain\SiteContent\Entity\PriceItem;
use App\Domain\SiteContent\Repository\ServicePriceListRepository;
use App\Domain\SiteContent\VO\PriceBlockType;
use App\Domain\SiteContent\VO\PricePrefix;
use App\Shared\ValueObject\EntityId;

final readonly class ReplaceServicePriceListHandler
{
    public function __construct(
        private ServicePriceListRepository $priceLists,
        private EntityIdGenerator $ids,
    ) {}

    public function handle(ReplaceServicePriceListCommand $command): void
    {
        $blocks = [];

        foreach (array_values($command->blocks) as $blockIndex => $block) {
            $blockId = isset($block['id']) && $block['id'] !== null && $block['id'] !== ''
                ? new EntityId((int) $block['id'])
                : $this->ids->next('site_price_block');

            $items = [];

            foreach (array_values($block['items'] ?? []) as $itemIndex => $item) {
                $itemId = isset($item['id']) && $item['id'] !== null && $item['id'] !== ''
                    ? new EntityId((int) $item['id'])
                    : $this->ids->next('site_price_item');

                $items[] = PriceItem::create(
                    $itemId,
                    (string) ($item['name'] ?? ''),
                    (string) ($item['price'] ?? ''),
                    PricePrefix::fromNullable(isset($item['prefix']) ? (string) $item['prefix'] : null),
                    isset($item['description']) ? (string) $item['description'] : null,
                    $itemIndex + 1,
                );
            }

            $blocks[] = PriceBlock::create(
                $blockId,
                PriceBlockType::fromString((string) ($block['type'] ?? '')),
                (string) ($block['title'] ?? ''),
                $items,
                $blockIndex + 1,
            );
        }

        $priceList = $this->priceLists->get();
        $priceList->replaceBlocks($blocks);
        $this->priceLists->save($priceList);
    }
}
