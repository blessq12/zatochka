<?php

namespace App\Application\ClientPortal\Query;

final readonly class GetLinkableGuestOrdersQuery
{
    public function __construct(
        public string $search = '',
        public int $limit = 50,
    ) {}
}
