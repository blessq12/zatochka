<?php

namespace App\Application\Identity\Command;

final readonly class LoginMasterCommand
{
    public function __construct(
        public string $email,
        public string $plainPassword,
    ) {}
}
