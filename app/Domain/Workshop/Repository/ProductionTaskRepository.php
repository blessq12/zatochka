<?php

namespace App\Domain\Workshop\Repository;

use App\Domain\Workshop\Entity\ProductionTask;
use App\Shared\ValueObject\EntityId;

interface ProductionTaskRepository
{
    public function save(ProductionTask $task): void;

    public function findById(EntityId $id): ?ProductionTask;

    public function getById(EntityId $id): ProductionTask;

    public function findByOrderItemId(EntityId $orderItemId): ?ProductionTask;

    /** @return list<ProductionTask> */
    public function listQueued(): array;
}
