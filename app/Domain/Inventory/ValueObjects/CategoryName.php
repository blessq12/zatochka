<?php

namespace App\Domain\Inventory\ValueObjects;

use InvalidArgumentException;

class CategoryName
{
    private string $value;

    private function __construct(string $value)
    {
        $this->ensureValidName($value);
        $this->value = trim($value);
    }

    private function ensureValidName(string $value): void
    {
        if (empty(trim($value))) {
            throw new InvalidArgumentException('Category name cannot be empty');
        }

        if (strlen($value) > 100) {
            throw new InvalidArgumentException('Category name cannot exceed 100 characters');
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

    public function equals(CategoryName $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
