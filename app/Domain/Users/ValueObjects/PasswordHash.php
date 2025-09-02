<?php

namespace App\Domain\Users\ValueObjects;

use InvalidArgumentException;

/**
 * Represents an already-hashed password.
 * Creation from plain text must be done via a PasswordHasherInterface in the application/infrastructure layer.
 */
class PasswordHash
{
    private string $hash;

    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public static function fromHash(string $hash): self
    {
        $hash = trim($hash);
        if ($hash === '') {
            throw new InvalidArgumentException('Password hash must not be empty');
        }

        // Basic sanity check: Laravel bcrypt/argon hashes usually start with $2y$ or $argon2
        if (strpos($hash, '$') !== 0) {
            throw new InvalidArgumentException('Invalid password hash format');
        }

        return new self($hash);
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->hash, (string) $other);
    }

    public function __toString(): string
    {
        return $this->hash;
    }

    public function toString(): string
    {
        return $this->hash;
    }
}
