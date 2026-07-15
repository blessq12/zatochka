<?php

namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Entity\Role;
use App\Shared\ValueObject\EntityId;

interface RoleRepository
{
    public function save(Role $role): void;

    public function findById(EntityId $id): ?Role;

    public function getById(EntityId $id): Role;
}
