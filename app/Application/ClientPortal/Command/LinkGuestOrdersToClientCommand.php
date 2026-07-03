<?php

namespace App\Application\ClientPortal\Command;

final readonly class LinkGuestOrdersToClientCommand
{
    public function __construct(
        public int $clientId,
    ) {}
}
