<?php

namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Entity\Master;
use App\Shared\ValueObject\EntityId;

interface MasterRepository
{
    public function save(Master $master): void;

    public function findById(EntityId $id): ?Master;

    public function getById(EntityId $id): Master;

    public function findByEmail(string $email): ?Master;

    public function emailExists(string $email, ?EntityId $exceptId = null): bool;

    public function delete(EntityId $id): void;
}
