<?php

namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Entity\MasterAccount;
use App\Shared\ValueObject\EntityId;

interface MasterAccountRepository
{
    public function save(MasterAccount $account): void;

    public function findById(EntityId $id): ?MasterAccount;

    public function getById(EntityId $id): MasterAccount;

    public function findByEmail(string $email): ?MasterAccount;

    public function emailExists(string $email, ?EntityId $exceptId = null): bool;
}
