<?php

namespace App\Application\Identity\Command;

final readonly class ChangeClientPortalPasswordCommand
{
    public function __construct(
        public int $clientId,
        public string $plainPassword,
    ) {}
}
