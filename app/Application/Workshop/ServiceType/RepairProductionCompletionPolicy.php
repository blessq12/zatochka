<?php

namespace App\Application\Workshop\ServiceType;

use App\Domain\Order\Entity\Order;
use App\Domain\Workshop\Entity\ProductionTask;
use App\Shared\Domain\DomainException;

/**
 * Repair: each non-rejected equipment position must have ≥1 work
 * (works are attached to equipment components but still tied to the order item).
 */
final class RepairProductionCompletionPolicy implements ProductionCompletionPolicy
{
    public function assertReadyToFinish(Order $order, ProductionTask $task): void
    {
        foreach ($order->items() as $item) {
            if ($item->isFullyRejected()) {
                continue;
            }

            foreach ($task->works() as $work) {
                if ($work->orderItemId->equals($item->id())) {
                    continue 2;
                }
            }

            throw new DomainException(sprintf(
                'Equipment item #%d has no completed works on its components.',
                $item->id()->value,
            ));
        }
    }
}
