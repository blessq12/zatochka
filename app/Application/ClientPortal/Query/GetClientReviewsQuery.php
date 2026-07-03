<?php

namespace App\Application\ClientPortal\Query;

final readonly class GetClientReviewsQuery
{
    public function __construct(
        public int $clientId,
    ) {}
}
