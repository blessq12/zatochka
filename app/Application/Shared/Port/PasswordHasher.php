<?php

namespace App\Application\Shared\Port;

interface PasswordHasher
{
    public function hash(string $plainPassword): string;

    public function check(string $plainPassword, string $hashedPassword): bool;
}
