<?php

namespace App\Domain\Finance\Repository;

use App\Domain\Finance\Entity\CashOperation;
use App\Shared\ValueObject\EntityId;

interface CashOperationRepository
{
    public function save(CashOperation $operation): void;

    public function findById(EntityId $id): ?CashOperation;

    public function getById(EntityId $id): CashOperation;
}
