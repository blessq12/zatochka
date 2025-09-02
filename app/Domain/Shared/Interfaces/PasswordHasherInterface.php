<?php

namespace App\Domain\Shared\Interfaces;

use App\Domain\Users\ValueObjects\PasswordHash;

interface PasswordHasherInterface
{
    public function hash(string $plain): PasswordHash;

    public function verify(string $plain, PasswordHash $hash): bool;
}
