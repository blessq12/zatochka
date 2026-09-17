<?php

namespace App\Domain\Warehouse\Aggregate;

use App\Domain\Warehouse\StockItemCategory;
use App\Shared\Domain\DomainException;

final class StockItem
{
    public function __construct(
        private ?int $id,
        private StockItemCategory $category,
        private string $name,
        private string $qtyOnHand,
        private string $unit,
    ) {
        $this->assertName($name);
        $this->assertUnit($unit);
        self::assertQty($qtyOnHand);
    }

    public static function create(
        StockItemCategory $category,
        string $name,
        string $unit,
        string $qtyOnHand = '0',
    ): self {
        return new self(
            null,
            $category,
            trim($name),
            self::normalizeQty($qtyOnHand),
            trim($unit),
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function category(): StockItemCategory
    {
        return $this->category;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function qtyOnHand(): string
    {
        return $this->qtyOnHand;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function rename(string $name, string $unit, StockItemCategory $category): void
    {
        $this->assertName($name);
        $this->assertUnit($unit);
        $this->name = trim($name);
        $this->unit = trim($unit);
        $this->category = $category;
    }

    public function receive(string $qty): void
    {
        $delta = self::normalizeQty($qty);
        if ((float) $delta <= 0) {
            throw new DomainException('receive qty must be > 0.');
        }

        $this->qtyOnHand = self::normalizeQty((string) ((float) $this->qtyOnHand + (float) $delta));
    }

    public function issue(string $qty): void
    {
        $delta = self::normalizeQty($qty);
        if ((float) $delta <= 0) {
            throw new DomainException('issue qty must be > 0.');
        }
        if ((float) $delta > (float) $this->qtyOnHand) {
            throw new DomainException('Insufficient stock.');
        }

        $this->qtyOnHand = self::normalizeQty((string) ((float) $this->qtyOnHand - (float) $delta));
    }

    private function assertName(string $name): void
    {
        if (trim($name) === '') {
            throw new DomainException('name is required.');
        }
        if (mb_strlen(trim($name)) > 255) {
            throw new DomainException('name is too long.');
        }
    }

    private function assertUnit(string $unit): void
    {
        if (trim($unit) === '') {
            throw new DomainException('unit is required.');
        }
        if (mb_strlen(trim($unit)) > 32) {
            throw new DomainException('unit is too long.');
        }
    }

    private static function assertQty(string $qty): void
    {
        if (! is_numeric($qty) || (float) $qty < 0) {
            throw new DomainException('qty must be a non-negative number.');
        }
    }

    public static function normalizeQty(string $qty): string
    {
        self::assertQty($qty);

        return number_format((float) $qty, 3, '.', '');
    }
}
