<?php

namespace App\Domain\Inventory\Entity;

use App\Domain\Inventory\VO\StockCategory;
use App\Domain\Inventory\VO\StockSku;
use App\Domain\Inventory\VO\UnitOfMeasure;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final class Material
{
    public function __construct(
        private readonly EntityId $id,
        private readonly StockSku $sku,
        private readonly string $name,
        private readonly UnitOfMeasure $unit,
        private readonly StockCategory $category,
        private Money $unitPrice = new Money('0.00'),
    ) {
        if (trim($this->name) === '') {
            throw new DomainException('Material name is required.');
        }

        $this->assertValidUnitPrice($this->unitPrice);
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function sku(): StockSku
    {
        return $this->sku;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function unit(): UnitOfMeasure
    {
        return $this->unit;
    }

    public function category(): StockCategory
    {
        return $this->category;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function changeUnitPrice(Money $unitPrice): void
    {
        $this->assertValidUnitPrice($unitPrice);
        $this->unitPrice = $unitPrice;
    }

    private function assertValidUnitPrice(Money $unitPrice): void
    {
        if ((float) $unitPrice->amount < 0) {
            throw new DomainException('Material unit price cannot be negative.');
        }
    }
}
