<?php

namespace App\Domain\CRM\Repository;

use App\Domain\CRM\Entity\Client;
use App\Shared\ValueObject\Email;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Phone;

interface ClientRepository
{
    public function save(Client $client): void;

    public function findById(EntityId $id): ?Client;

    public function getById(EntityId $id): Client;

    public function findByPhone(Phone $phone): ?Client;

    public function findByEmail(Email $email): ?Client;
}
