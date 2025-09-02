<?php

namespace App\Infrastructure\Services;

use App\Domain\Shared\Interfaces\PasswordHasherInterface;
use App\Domain\Users\ValueObjects\PasswordHash;
use Illuminate\Support\Facades\Hash;

class LaravelPasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plain): PasswordHash
    {
        return PasswordHash::fromHash(Hash::make($plain));
    }

    public function verify(string $plain, PasswordHash $hash): bool
    {
        return Hash::check($plain, (string) $hash);
    }
}
