<?php

namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Entity\Manager;
use App\Shared\ValueObject\EntityId;

interface ManagerRepository
{
    public function save(Manager $manager): void;

    public function findById(EntityId $id): ?Manager;

    public function getById(EntityId $id): Manager;

    public function findByEmail(string $email): ?Manager;

    public function emailExists(string $email, ?EntityId $exceptId = null): bool;

    public function delete(EntityId $id): void;
}
