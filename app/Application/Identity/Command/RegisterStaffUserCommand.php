<?php

namespace App\Application\Identity\Command;

final readonly class RegisterStaffUserCommand
{
    public function __construct(
        public int $userId,
        public string $name,
        public string $email,
        public string $role,
        public string $plainPassword,
    ) {}
}
