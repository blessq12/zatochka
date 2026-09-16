<?php

namespace App\Application\Identity\Command;

final readonly class LoginClientPortalCommand
{
    public function __construct(
        public string $phone,
        public string $plainPassword,
    ) {}
}
