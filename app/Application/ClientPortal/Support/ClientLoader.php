<?php

namespace App\Application\ClientPortal\Support;

use App\Domain\ClientPortal\Entity\Client;
use App\Domain\ClientPortal\Exception\ClientNotFoundException;
use App\Domain\ClientPortal\Repository\ClientRepositoryInterface;

final class ClientLoader
{
    public function __construct(
        private ClientRepositoryInterface $clients,
    ) {}

    public function load(int $clientId): Client
    {
        $client = $this->clients->findById($clientId);

        if ($client === null) {
            throw ClientNotFoundException::withId($clientId);
        }

        return $client;
    }
}
