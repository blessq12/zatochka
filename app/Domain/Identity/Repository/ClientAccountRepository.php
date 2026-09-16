<?php

namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Entity\ClientAccount;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Phone;

interface ClientAccountRepository
{
    public function save(ClientAccount $account): void;

    public function findById(EntityId $id): ?ClientAccount;

    public function findByClientId(EntityId $clientId): ?ClientAccount;

    public function findByPhone(Phone $phone): ?ClientAccount;

    public function getByClientId(EntityId $clientId): ClientAccount;
}
