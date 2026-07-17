<?php

namespace App\Application\Identity\Command;

final readonly class ChangeStaffPasswordCommand
{
    public function __construct(
        public int $userId,
        public string $plainPassword,
    ) {}
}
