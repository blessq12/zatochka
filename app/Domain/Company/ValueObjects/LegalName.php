<?php

namespace App\Domain\Company\ValueObjects;

use InvalidArgumentException;

class LegalName
{
    private string $value;

    private function __construct(string $value)
    {
        $this->ensureValidLegalName($value);
        $this->value = trim($value);
    }

    private function ensureValidLegalName(string $value): void
    {
        if (empty(trim($value))) {
            throw new InvalidArgumentException('Legal name cannot be empty');
        }

        if (strlen($value) > 500) {
            throw new InvalidArgumentException('Legal name cannot exceed 500 characters');
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

    public function equals(LegalName $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
