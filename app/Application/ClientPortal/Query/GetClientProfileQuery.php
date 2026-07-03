<?php

namespace App\Application\ClientPortal\Query;

final readonly class GetClientProfileQuery
{
    public function __construct(
        public int $clientId,
    ) {}
}
