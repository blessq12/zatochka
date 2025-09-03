<?php

namespace App\Domain\Inventory\ValueObjects;

use App\Domain\Shared\ValueObjects\UuidValueObject;

class WarehouseId extends UuidValueObject
{
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
