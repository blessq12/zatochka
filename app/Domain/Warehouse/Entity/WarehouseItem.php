<?php

namespace App\Domain\Warehouse\Entity;

use App\Domain\Warehouse\Enum\WarehouseItemType;
use App\Domain\Warehouse\Exception\WarehousePolicyViolation;

final class WarehouseItem
{
    public function __construct(
        private ?int $id,
        private string $name,
        private string $sku,
        private WarehouseItemType $type,
        private string $quantity,
        private string $unit,
        private string $price,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function sku(): string
    {
        return $this->sku;
    }

    public function type(): WarehouseItemType
    {
        return $this->type;
    }

    public function quantity(): string
    {
        return $this->quantity;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function price(): string
    {
        return $this->price;
    }

    public static function create(
        string $name,
        string $sku,
        WarehouseItemType $type,
        string $quantity,
        string $unit,
        string $price,
    ): self {
        return new self(null, $name, $sku, $type, $quantity, $unit, $price);
    }

    public function receive(string $quantity): self
    {
        if (bccomp($quantity, '0', 3) <= 0) {
            throw new WarehousePolicyViolation('Количество прихода должно быть больше нуля.');
        }

        $clone = clone $this;
        $clone->quantity = bcadd($this->quantity, $quantity, 3);

        return $clone;
    }

    public function writeOff(string $quantity): self
    {
        if (bccomp($quantity, '0', 3) <= 0) {
            throw new WarehousePolicyViolation('Количество списания должно быть больше нуля.');
        }

        $remaining = bcsub($this->quantity, $quantity, 3);

        if (bccomp($remaining, '0', 3) < 0) {
            throw new WarehousePolicyViolation('Недостаточно остатка на складе.');
        }

        $clone = clone $this;
        $clone->quantity = $remaining;

        return $clone;
    }
}
