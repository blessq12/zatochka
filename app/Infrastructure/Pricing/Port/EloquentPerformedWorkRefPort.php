<?php

namespace App\Infrastructure\Pricing\Port;

use App\Application\Pricing\Port\PerformedWorkRefDTO;
use App\Application\Pricing\Port\PerformedWorkRefPort;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Workshop\Model\PerformedWorkModel;
use App\Infrastructure\Workshop\Model\ProductionTaskModel;

final readonly class EloquentPerformedWorkRefPort implements PerformedWorkRefPort
{
    public function findById(int $performedWorkId): ?PerformedWorkRefDTO
    {
        $work = PerformedWorkModel::query()->find($performedWorkId);

        if ($work === null) {
            return null;
        }

        $orderItemId = (int) $work->order_item_id;
        $orderId = OrderItemModel::query()->whereKey($orderItemId)->value('order_id');

        if ($orderId === null) {
            return null;
        }

        $task = ProductionTaskModel::query()->find($work->production_task_id);

        if ($task === null || (string) $task->order_id !== (string) $orderId) {
            return null;
        }

        return new PerformedWorkRefDTO(
            (int) $work->id,
            $orderItemId,
            (string) $orderId,
        );
    }
}
