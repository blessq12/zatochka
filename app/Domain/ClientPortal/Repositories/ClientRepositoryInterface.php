<?php

namespace App\Domain\ClientPortal\Repositories;

use App\Domain\ClientPortal\Entities\Client;

interface ClientRepositoryInterface
{
    public function findById(int $id): ?Client;

    public function findByPhone(string $phone): ?Client;

    public function save(Client $client): Client;
}
