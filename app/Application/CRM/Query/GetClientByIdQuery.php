<?php

namespace App\Application\CRM\Query;

final readonly class GetClientByIdQuery
{
    public function __construct(
        public int $clientId,
    ) {}
}
