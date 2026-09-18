<?php

namespace App\Domain\Crm\Repository;

use App\Domain\Crm\Aggregate\Client;

interface ClientRepository extends ActorRepository
{
    /**
     * @return list<Client>
     */
    public function searchByNameOrPhone(string $query, int $limit = 20): array;

    /**
     * @return list<Client>
     */
    public function recent(int $limit = 8): array;
}
