<?php

namespace App\Application\ClientPortal\Query;

final readonly class GetClientOrdersQuery
{
    public function __construct(
        public int $clientId,
        public bool $history,
        public int $page = 1,
        public int $perPage = 20,
    ) {}
}
