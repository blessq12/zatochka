<?php

namespace App\Domain\Pricing\Repository;

use App\Domain\Pricing\Entity\Estimate;
use App\Shared\ValueObject\EntityId;

interface EstimateRepository
{
    public function save(Estimate $estimate): void;

    public function findById(EntityId $id): ?Estimate;

    public function getById(EntityId $id): Estimate;

    public function findByOrderItemId(EntityId $orderItemId): ?Estimate;
}
