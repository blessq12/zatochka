<?php

namespace App\Application\CRM\Command;

final readonly class ChangeClientPortalPasswordCommand
{
    public function __construct(
        public int $clientId,
        public string $password,
    ) {}
}
