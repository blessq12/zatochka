<?php

namespace App\Application\Identity\Command;

final readonly class RegisterMasterCommand
{
    public function __construct(
        public int $masterId,
        public string $name,
        public string $email,
        public string $plainPassword,
    ) {}
}
