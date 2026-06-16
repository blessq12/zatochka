<?php

namespace App\Domain\Warehouse\Entity;

final class WarehouseItem
{
    public function __construct(
        private ?int $id,
        private string $name,
        private string $sku,
        private ?string $categoryName,
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

    public function categoryName(): ?string
    {
        return $this->categoryName;
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
}
