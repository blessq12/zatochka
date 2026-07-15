<?php

namespace App\Infrastructure\Pricing\ReadModel;

use App\Application\Pricing\DTO\EstimateDTO;
use App\Application\Pricing\ReadPort\EstimateReadPort;
use App\Infrastructure\Pricing\Mapper\EstimateMapper;
use App\Infrastructure\Pricing\Model\EstimateModel;

final readonly class EloquentEstimateReadModel implements EstimateReadPort
{
    public function __construct(
        private EstimateMapper $mapper,
    ) {}

    public function findById(int $estimateId): ?EstimateDTO
    {
        $model = EstimateModel::query()->with('itemPrice')->find($estimateId);

        return $model === null ? null : $this->mapper->toDTO($model);
    }

    public function findByOrderItemId(int $orderItemId): ?EstimateDTO
    {
        $model = EstimateModel::query()
            ->with('itemPrice')
            ->where('order_item_id', $orderItemId)
            ->first();

        return $model === null ? null : $this->mapper->toDTO($model);
    }
}
