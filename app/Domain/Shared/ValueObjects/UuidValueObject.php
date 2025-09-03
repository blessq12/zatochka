<?php

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

abstract class UuidValueObject
{
    protected string $value;

    protected function __construct(string $value)
    {
        $this->ensureValidUuid($value);
        $this->value = $value;
    }

    private function ensureValidUuid(string $value): void
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value)) {
            throw new InvalidArgumentException("Invalid UUID format: {$value}");
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(UuidValueObject $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): self
    {
        return new static($value);
    }

    public static function generate(): self
    {
        return new static(\Ramsey\Uuid\Uuid::uuid4()->toString());
    }
}
