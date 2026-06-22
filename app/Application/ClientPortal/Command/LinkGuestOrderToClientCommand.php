<?php

namespace App\Application\ClientPortal\Command;

final readonly class LinkGuestOrderToClientCommand
{
    public function __construct(
        public int $clientId,
        public int $orderId,
    ) {}
}
