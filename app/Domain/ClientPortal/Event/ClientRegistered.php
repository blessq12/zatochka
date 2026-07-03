<?php

namespace App\Domain\ClientPortal\Event;

use App\Domain\ClientPortal\Entity\Client;

final readonly class ClientRegistered
{
    public function __construct(
        public Client $client,
    ) {}
}
