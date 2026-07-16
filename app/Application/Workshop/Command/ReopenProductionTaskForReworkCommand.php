<?php

namespace App\Application\Workshop\Command;

final readonly class ReopenProductionTaskForReworkCommand
{
    public function __construct(
        public string $orderId,
    ) {}
}
