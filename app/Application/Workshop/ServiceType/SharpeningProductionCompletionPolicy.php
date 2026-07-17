<?php

namespace App\Application\Workshop\ServiceType;

use App\Application\Workshop\DTO\OrderProductionContextDTO;
use App\Domain\Workshop\Entity\ProductionTask;
use App\Shared\Domain\DomainException;

/**
 * Sharpening: each non-rejected tool position must have ≥1 performed work.
 */
final class SharpeningProductionCompletionPolicy implements ProductionCompletionPolicy
{
    public function assertReadyToFinish(OrderProductionContextDTO $context, ProductionTask $task): void
    {
        foreach ($context->items as $item) {
            if ($item->fullyRejected) {
                continue;
            }

            foreach ($task->works() as $work) {
                if ($work->orderItemId->value === $item->orderItemId) {
                    continue 2;
                }
            }

            throw new DomainException(sprintf(
                'Item #%d has no completed works.',
                $item->orderItemId,
            ));
        }
    }
}
