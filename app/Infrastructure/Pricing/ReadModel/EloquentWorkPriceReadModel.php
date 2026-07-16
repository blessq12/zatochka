<?php

namespace App\Infrastructure\Pricing\ReadModel;

use App\Application\Pricing\DTO\WorkPriceDTO;
use App\Application\Pricing\ReadPort\WorkPriceReadPort;
use App\Infrastructure\Pricing\Mapper\WorkPriceMapper;
use App\Infrastructure\Pricing\Model\WorkPriceModel;
use App\Infrastructure\Workshop\Model\MasterCommentModel;
use App\Infrastructure\Workshop\Model\ProductionTaskModel;

final readonly class EloquentWorkPriceReadModel implements WorkPriceReadPort
{
    public function __construct(
        private WorkPriceMapper $mapper,
    ) {}

    public function findByMasterCommentId(int $masterCommentId): ?WorkPriceDTO
    {
        $model = WorkPriceModel::query()
            ->where('master_comment_id', $masterCommentId)
            ->first();

        return $model === null ? null : $this->mapper->toDTO($model);
    }

    public function findByOrderId(int $orderId): array
    {
        $taskId = ProductionTaskModel::query()
            ->where('order_id', $orderId)
            ->value('id');

        if ($taskId === null) {
            return [];
        }

        $masterCommentIds = MasterCommentModel::query()
            ->where('production_task_id', $taskId)
            ->whereNotNull('order_item_id')
            ->pluck('id');

        if ($masterCommentIds->isEmpty()) {
            return [];
        }

        return WorkPriceModel::query()
            ->whereIn('master_comment_id', $masterCommentIds)
            ->get()
            ->map(fn (WorkPriceModel $model): WorkPriceDTO => $this->mapper->toDTO($model))
            ->all();
    }
}
