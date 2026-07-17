<?php

namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Entity\StaffUser;
use App\Shared\ValueObject\EntityId;

interface StaffUserRepository
{
    public function save(StaffUser $user): void;

    public function findById(EntityId $id): ?StaffUser;

    public function getById(EntityId $id): StaffUser;

    public function emailExists(string $email, ?EntityId $exceptId = null): bool;
}
