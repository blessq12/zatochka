<?php

namespace App\Infrastructure\SiteContent\Repository;

use App\Domain\SiteContent\Entity\ServicePriceList;
use App\Domain\SiteContent\Repository\ServicePriceListRepository;
use App\Infrastructure\SiteContent\Mapper\ServicePriceListMapper;
use App\Infrastructure\SiteContent\Model\PriceBlockModel;
use App\Infrastructure\SiteContent\Model\PriceItemModel;
use Illuminate\Support\Facades\DB;

final readonly class EloquentServicePriceListRepository implements ServicePriceListRepository
{
    public function __construct(
        private ServicePriceListMapper $mapper,
    ) {}

    public function get(): ServicePriceList
    {
        $models = PriceBlockModel::query()
            ->with('items')
            ->orderBy('sort_order')
            ->get();

        return $this->mapper->toDomain($models);
    }

    public function save(ServicePriceList $priceList): void
    {
        DB::transaction(function () use ($priceList): void {
            $payload = $this->mapper->toPersistence($priceList);
            $blockIds = array_column($payload['blocks'], 'id');
            $itemIds = array_column($payload['items'], 'id');

            if ($itemIds === []) {
                PriceItemModel::query()->delete();
            } else {
                PriceItemModel::query()->whereNotIn('id', $itemIds)->delete();
            }

            if ($blockIds === []) {
                PriceBlockModel::query()->delete();
            } else {
                PriceBlockModel::query()->whereNotIn('id', $blockIds)->delete();
            }

            foreach ($payload['blocks'] as $block) {
                PriceBlockModel::query()->updateOrCreate(
                    ['id' => $block['id']],
                    $block,
                );
            }

            foreach ($payload['items'] as $item) {
                PriceItemModel::query()->updateOrCreate(
                    ['id' => $item['id']],
                    $item,
                );
            }
        });
    }
}
