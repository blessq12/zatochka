<?php

namespace App\Application\Catalog\QueryHandler;

use App\Application\Catalog\Query\GetPublicBootstrapQuery;
use App\Domain\Catalog\Repository\PriceBlockRepositoryInterface;
use App\Domain\Catalog\Repository\PriceItemRepositoryInterface;
use App\Domain\Catalog\Repository\SiteSettingRepositoryInterface;

final class GetPublicBootstrapQueryHandler
{
    private const SETTING_KEYS = ['contacts', 'schedule', 'delivery_info', 'company'];

    public function __construct(
        private PriceBlockRepositoryInterface $priceBlocks,
        private PriceItemRepositoryInterface $priceItems,
        private SiteSettingRepositoryInterface $siteSettings,
    ) {}

    /**
     * @return array{prices: list<array<string, mixed>>, contacts: array, schedule: array, delivery_info: array, company: array}
     */
    public function handle(GetPublicBootstrapQuery $query): array
    {
        $settings = $this->siteSettings->getValuesByKeys(self::SETTING_KEYS);

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

        return [
            'prices' => $prices,
            'contacts' => $settings['contacts'] ?? [],
            'schedule' => $settings['schedule'] ?? [],
            'delivery_info' => $settings['delivery_info'] ?? [],
            'company' => $settings['company'] ?? [],
        ];
    }
}
