<?php

namespace App\Infrastructure\Pricing\ReadModel;

use App\Application\Pricing\DTO\WorkPriceDTO;
use App\Application\Pricing\ReadPort\WorkPriceReadPort;
use App\Infrastructure\Pricing\Mapper\WorkPriceMapper;
use App\Infrastructure\Pricing\Model\WorkPriceModel;
use App\Infrastructure\Workshop\Model\PerformedWorkModel;
use App\Infrastructure\Workshop\Model\ProductionTaskModel;

final readonly class EloquentWorkPriceReadModel implements WorkPriceReadPort
{
    public function __construct(
        private WorkPriceMapper $mapper,
    ) {}

    public function findByPerformedWorkId(int $performedWorkId): ?WorkPriceDTO
    {
        $model = WorkPriceModel::query()
            ->where('performed_work_id', $performedWorkId)
            ->first();

        return $model === null ? null : $this->mapper->toDTO($model);
    }

    public function findByOrderId(string $orderId): array
    {
        $taskId = ProductionTaskModel::query()
            ->where('order_id', $orderId)
            ->value('id');

        if ($taskId === null) {
            return [];
        }

        $performedWorkIds = PerformedWorkModel::query()
            ->where('production_task_id', $taskId)
            ->pluck('id');

        if ($performedWorkIds->isEmpty()) {
            return [];
        }

        return WorkPriceModel::query()
            ->whereIn('performed_work_id', $performedWorkIds)
            ->get()
            ->map(fn (WorkPriceModel $model): WorkPriceDTO => $this->mapper->toDTO($model))
            ->all();
    }
}
