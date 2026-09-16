<?php

namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Entity\ManagerAccount;
use App\Shared\ValueObject\EntityId;

interface ManagerAccountRepository
{
    public function save(ManagerAccount $account): void;

    public function findById(EntityId $id): ?ManagerAccount;

    public function getById(EntityId $id): ManagerAccount;

    public function findByEmail(string $email): ?ManagerAccount;

    public function emailExists(string $email, ?EntityId $exceptId = null): bool;
}
