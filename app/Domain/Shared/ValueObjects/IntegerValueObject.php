<?php

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

abstract class IntegerValueObject
{
    protected int $value;

    protected function __construct(int $value)
    {
        $this->ensureValidInteger($value);
        $this->value = $value;
    }

    private function ensureValidInteger(int $value): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException("Integer value must be non-negative: {$value}");
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(IntegerValueObject $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public static function fromInt(int $value): self
    {
        return new static($value);
    }

    public static function fromString(string $value): self
    {
        if (!is_numeric($value) || (int) $value != $value) {
            throw new InvalidArgumentException("Invalid integer string: {$value}");
        }
        return new static((int) $value);
    }
}
