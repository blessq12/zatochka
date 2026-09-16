<?php

namespace App\Application\Identity\Command;

final readonly class ChangeMasterPasswordCommand
{
    public function __construct(
        public int $masterId,
        public string $plainPassword,
    ) {}
}
