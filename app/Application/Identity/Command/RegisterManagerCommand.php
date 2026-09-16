<?php

namespace App\Application\Identity\Command;

final readonly class RegisterManagerCommand
{
    public function __construct(
        public int $managerId,
        public string $name,
        public string $email,
        public string $plainPassword,
    ) {}
}
