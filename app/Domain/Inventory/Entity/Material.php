<?php

namespace App\Domain\Inventory\Entity;

use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class Material
{
    public function __construct(
        private readonly EntityId $id,
        private readonly string $sku,
        private readonly string $name,
        private readonly string $unit,
    ) {
        if (trim($this->sku) === '' || trim($this->name) === '' || trim($this->unit) === '') {
            throw new DomainException('Material sku, name and unit are required.');
        }
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function sku(): string
    {
        return $this->sku;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function unit(): string
    {
        return $this->unit;
    }
}
