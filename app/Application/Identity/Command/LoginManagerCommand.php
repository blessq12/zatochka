<?php

namespace App\Application\Identity\Command;

final readonly class LoginManagerCommand
{
    public function __construct(
        public string $email,
        public string $plainPassword,
    ) {}
}
