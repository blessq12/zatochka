<?php

namespace App\Application\ClientPortal\QueryHandler;

use App\Application\ClientPortal\Query\GetClientProfileQuery;
use App\Application\ClientPortal\Support\ClientLoader;
use App\Domain\ClientPortal\Entity\Client;

final class GetClientProfileQueryHandler
{
    public function __construct(
        private ClientLoader $clientLoader,
    ) {}

    public function handle(GetClientProfileQuery $query): Client
    {
        return $this->clientLoader->load($query->clientId);
    }
}
