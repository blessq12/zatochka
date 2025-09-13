<?php

namespace App\Domain\Client\Repository;

use App\Domain\Client\Entity\Client;

interface ClientRepository
{
    public function create(array $data): Client;

    public function get(string $id): ?Client;

    public function update(Client $client, array $data): Client;

    public function delete(string $id): bool;

    public function existsByPhone(string $phone): bool;

    public function findByPhone(string $phone): ?Client;
}
