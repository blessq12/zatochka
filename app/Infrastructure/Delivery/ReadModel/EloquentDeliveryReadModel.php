<?php

namespace App\Infrastructure\Delivery\ReadModel;

use App\Application\Delivery\DTO\DeliveryRequestDTO;
use App\Application\Delivery\ReadPort\DeliveryReadPort;
use App\Infrastructure\Delivery\Mapper\DeliveryRequestMapper;
use App\Infrastructure\Delivery\Model\DeliveryRequestModel;

final readonly class EloquentDeliveryReadModel implements DeliveryReadPort
{
    public function __construct(
        private DeliveryRequestMapper $mapper,
    ) {}

    public function findById(int $deliveryRequestId): ?DeliveryRequestDTO
    {
        $model = DeliveryRequestModel::query()->find($deliveryRequestId);

        return $model === null ? null : $this->mapper->toDTO($model);
    }

    public function findByOrderId(string $orderId): ?DeliveryRequestDTO
    {
        $model = DeliveryRequestModel::query()
            ->where('order_id', $orderId)
            ->first();

        return $model === null ? null : $this->mapper->toDTO($model);
    }
}
