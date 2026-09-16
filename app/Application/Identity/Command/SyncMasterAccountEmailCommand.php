<?php

namespace App\Application\Identity\Command;

final readonly class SyncMasterAccountEmailCommand
{
    public function __construct(
        public int $masterId,
        public string $email,
    ) {}
}
