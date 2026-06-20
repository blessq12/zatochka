<?php

namespace App\Application\Identity\Command;

final readonly class RegisterMasterCommand
{
    public function __construct(
        public string $name,
        public string $surname,
        public string $email,
        public ?string $phone,
        public string $password,
        public bool $notificationsEnabled,
    ) {}
}
