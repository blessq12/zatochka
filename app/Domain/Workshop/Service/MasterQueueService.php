<?php

namespace App\Domain\Workshop\Service;

use App\Domain\Workshop\Entity\ProductionTask;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class MasterQueueService
{
    public function __construct(
        private ProductionTaskRepository $tasks,
    ) {}

    public function openTask(EntityId $taskId, EntityId $orderItemId): ProductionTask
    {
        if ($this->tasks->findByOrderItemId($orderItemId) !== null) {
            throw new DomainException('Production task already exists for this order item.');
        }

        return ProductionTask::open($taskId, $orderItemId);
    }
}
