<?php

namespace App\Domain\ClientPortal\Repository;

use App\Domain\ClientPortal\Entity\Client;

interface ClientRepositoryInterface
{
    public function findById(int $id): ?Client;

    public function findByPhone(string $phone): ?Client;

    public function save(Client $client): Client;
}
