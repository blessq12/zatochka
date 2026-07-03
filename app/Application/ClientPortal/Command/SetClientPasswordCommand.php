<?php

namespace App\Application\ClientPortal\Command;

final readonly class SetClientPasswordCommand
{
    public function __construct(
        public int $clientId,
        public string $password,
    ) {}
}
