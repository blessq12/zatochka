<?php

namespace App\Domain\Inventory\ValueObjects;

use InvalidArgumentException;

class SKU
{
    private string $value;

    private function __construct(string $value)
    {
        $this->ensureValidSKU($value);
        $this->value = $value;
    }

    private function ensureValidSKU(string $value): void
    {
        if (empty(trim($value))) {
            throw new InvalidArgumentException('SKU cannot be empty');
        }

        if (strlen($value) > 100) {
            throw new InvalidArgumentException('SKU cannot exceed 100 characters');
        }

        // SKU должен содержать только буквы, цифры, дефисы и подчёркивания
        if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $value)) {
            throw new InvalidArgumentException('SKU can only contain letters, numbers, hyphens and underscores');
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

    public function equals(SKU $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
