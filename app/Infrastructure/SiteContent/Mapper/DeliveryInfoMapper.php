<?php

namespace App\Infrastructure\SiteContent\Mapper;

use App\Domain\SiteContent\Entity\DeliveryAdvantage;
use App\Domain\SiteContent\Entity\DeliveryInfo;
use App\Infrastructure\SiteContent\Model\DeliveryInfoModel;
use App\Shared\ValueObject\EntityId;

final class DeliveryInfoMapper
{
    public function toDomain(DeliveryInfoModel $model): DeliveryInfo
    {
        $advantages = [];

        foreach ((array) $model->advantages as $advantage) {
            $advantages[] = new DeliveryAdvantage(
                (string) ($advantage['title'] ?? ''),
                (string) ($advantage['description'] ?? ''),
            );
        }

        return DeliveryInfo::reconstitute(
            new EntityId((int) $model->id),
            array_values((array) $model->free_conditions),
            $advantages,
        );
    }

    /** @return array<string, mixed> */
    public function toPersistence(DeliveryInfo $deliveryInfo): array
    {
        return [
            'id' => $deliveryInfo->id()->value,
            'free_conditions' => $deliveryInfo->freeConditions(),
            'advantages' => array_map(
                static fn (DeliveryAdvantage $advantage): array => $advantage->toArray(),
                $deliveryInfo->advantages(),
            ),
        ];
    }
}
