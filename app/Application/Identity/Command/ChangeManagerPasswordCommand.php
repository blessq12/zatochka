<?php

namespace App\Application\Identity\Command;

final readonly class ChangeManagerPasswordCommand
{
    public function __construct(
        public int $managerId,
        public string $plainPassword,
    ) {}
}
