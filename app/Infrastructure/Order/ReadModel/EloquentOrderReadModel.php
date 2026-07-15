<?php

namespace App\Infrastructure\Order\ReadModel;

use App\Application\Order\DTO\OrderDTO;
use App\Application\Order\ReadPort\OrderReadPort;
use App\Infrastructure\Order\Mapper\OrderMapper;
use App\Infrastructure\Order\Model\OrderModel;

final readonly class EloquentOrderReadModel implements OrderReadPort
{
    public function __construct(
        private OrderMapper $mapper,
    ) {}

    public function findById(int $orderId): ?OrderDTO
    {
        $model = OrderModel::query()->with(['items.reception'])->find($orderId);

        return $model === null ? null : $this->mapper->toDTO($model);
    }

    public function listByClientId(int $clientId): array
    {
        return OrderModel::query()
            ->with(['items.reception'])
            ->where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($model) => $this->mapper->toDTO($model))
            ->all();
    }
}
