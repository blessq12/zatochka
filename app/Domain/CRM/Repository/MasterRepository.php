<?php

namespace App\Domain\CRM\Repository;

use App\Domain\CRM\Entity\Master;
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
