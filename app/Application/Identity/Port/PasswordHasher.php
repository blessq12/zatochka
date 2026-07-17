<?php

namespace App\Application\Identity\Port;

interface PasswordHasher
{
    public function hash(string $plainPassword): string;
}
