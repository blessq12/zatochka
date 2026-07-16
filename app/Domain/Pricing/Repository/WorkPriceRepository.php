<?php

namespace App\Domain\Pricing\Repository;

use App\Domain\Pricing\Entity\WorkPrice;
use App\Shared\ValueObject\EntityId;

interface WorkPriceRepository
{
    public function getById(EntityId $id): WorkPrice;

    public function findByPerformedWorkId(EntityId $performedWorkId): ?WorkPrice;

    public function save(WorkPrice $workPrice): void;

    /**
     * @param list<int> $performedWorkIds
     */
    public function deleteByPerformedWorkIds(array $performedWorkIds): void;

    public function deleteByOrderId(string $orderId): void;
}
