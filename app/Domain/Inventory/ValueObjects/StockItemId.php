<?php

namespace App\Domain\Inventory\ValueObjects;

use App\Domain\Shared\ValueObjects\UuidValueObject;

class StockItemId extends UuidValueObject
{
    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
