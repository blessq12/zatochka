<?php

namespace App\Domain\Company\ValueObjects;

use InvalidArgumentException;

class BranchCode
{
    private string $value;

    private function __construct(string $value)
    {
        $this->ensureValidCode($value);
        $this->value = trim($value);
    }

    private function ensureValidCode(string $value): void
    {
        if (empty(trim($value))) {
            throw new InvalidArgumentException('Branch code cannot be empty');
        }

        if (strlen($value) > 50) {
            throw new InvalidArgumentException('Branch code cannot exceed 50 characters');
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
            throw new InvalidArgumentException('Branch code can only contain letters, numbers, hyphens and underscores');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(BranchCode $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function isMain(): bool
    {
        return $this->value === 'main';
    }
}
