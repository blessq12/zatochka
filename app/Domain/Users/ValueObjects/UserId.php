<?php

namespace App\Domain\Users\ValueObjects;

use Ramsey\Uuid\Uuid as RamseyUuid;
use InvalidArgumentException;

class UserId
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function new(): self
    {
        return new self(RamseyUuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        $value = trim($value);
        if ($value === '' || !RamseyUuid::isValid($value)) {
            throw new InvalidArgumentException('Invalid UUID for UserId');
        }
        return new self($value);
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
