<?php

namespace App\Application\Workshop\ServiceType;

use App\Domain\Order\Entity\Order;
use App\Domain\Workshop\Entity\ProductionTask;
use App\Shared\Domain\DomainException;

/**
 * Sharpening: each non-rejected tool position must have ≥1 performed work.
 */
final class SharpeningProductionCompletionPolicy implements ProductionCompletionPolicy
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
                'Item #%d has no completed works.',
                $item->id()->value,
            ));
        }
    }
}
