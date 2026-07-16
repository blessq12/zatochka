<?php

namespace App\Domain\Pricing\Repository;

use App\Domain\Pricing\Entity\WorkPrice;
use App\Shared\ValueObject\EntityId;

interface WorkPriceRepository
{
    public function getById(EntityId $id): WorkPrice;

    public function findByMasterCommentId(EntityId $masterCommentId): ?WorkPrice;

    public function save(WorkPrice $workPrice): void;
}
