<?php

namespace App\Application\Workshop\ServiceType;

use App\Application\Workshop\DTO\OrderProductionContextDTO;
use App\Domain\Workshop\Entity\ProductionTask;
use App\Shared\Domain\DomainException;

/**
 * Repair: each non-rejected equipment position must have ≥1 work
 * (works are attached to equipment components but still tied to the order item).
 */
final class RepairProductionCompletionPolicy implements ProductionCompletionPolicy
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
                'Equipment item #%d has no completed works on its components.',
                $item->orderItemId,
            ));
        }
    }
}
