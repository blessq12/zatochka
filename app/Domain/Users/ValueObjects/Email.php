<?php

namespace App\Domain\Users\ValueObjects;

use InvalidArgumentException;

class Email
{
    private string $value;

    private function __construct(string $normalizedEmail)
    {
        $this->value = $normalizedEmail;
    }

    public static function fromString(string $email): self
    {
        $email = trim($email);
        if ($email === '') {
            throw new InvalidArgumentException('Email must not be empty');
        }

        // Normalize to lowercase for identity semantics
        $normalized = mb_strtolower($email);

        if (filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Invalid email format: ' . $email);
        }

        return new self($normalized);
    }

    public function equals(self $other): bool
    {
        return $this->value === (string) $other;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
