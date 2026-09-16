<?php

namespace App\Application\Identity\Command;

final readonly class SyncManagerAccountEmailCommand
{
    public function __construct(
        public int $managerId,
        public string $email,
    ) {}
}
