<?php

namespace App\Infrastructure\SiteContent\Repository;

use App\Domain\SiteContent\Entity\DeliveryInfo;
use App\Domain\SiteContent\Repository\DeliveryInfoRepository;
use App\Infrastructure\SiteContent\Mapper\DeliveryInfoMapper;
use App\Infrastructure\SiteContent\Model\DeliveryInfoModel;
use App\Shared\Domain\DomainException;

final readonly class EloquentDeliveryInfoRepository implements DeliveryInfoRepository
{
    public function __construct(
        private DeliveryInfoMapper $mapper,
    ) {}

    public function get(): DeliveryInfo
    {
        $model = DeliveryInfoModel::query()->find(DeliveryInfo::SINGLETON_ID);

        if ($model === null) {
            throw new DomainException('Delivery info is not configured.');
        }

        return $this->mapper->toDomain($model);
    }

    public function save(DeliveryInfo $deliveryInfo): void
    {
        $payload = $this->mapper->toPersistence($deliveryInfo);

        DeliveryInfoModel::query()->updateOrCreate(
            ['id' => $payload['id']],
            $payload,
        );
    }
}
