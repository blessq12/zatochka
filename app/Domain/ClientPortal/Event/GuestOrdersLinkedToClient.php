<?php

namespace App\Domain\ClientPortal\Event;

final readonly class GuestOrdersLinkedToClient
{
    public function __construct(
        public int $clientId,
        public int $linkedCount,
    ) {}
}
