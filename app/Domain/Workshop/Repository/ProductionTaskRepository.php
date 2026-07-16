<?php

namespace App\Domain\Workshop\Repository;

use App\Domain\Order\VO\OrderId;
use App\Domain\Workshop\Entity\ProductionTask;
use App\Shared\ValueObject\EntityId;

interface ProductionTaskRepository
{
    public function save(ProductionTask $task): void;

    public function findById(EntityId $id): ?ProductionTask;

    public function getById(EntityId $id): ProductionTask;

    public function findByOrderId(OrderId $orderId): ?ProductionTask;

    /** @return list<ProductionTask> */
    public function listQueued(): array;
}
