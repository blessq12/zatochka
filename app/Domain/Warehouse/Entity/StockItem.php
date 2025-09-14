<?php

namespace App\Domain\Warehouse\Entity;

readonly class StockItem
{
    public function __construct(
        public ?int $id,
        public int $warehouseId,
        public int $categoryId,
        public string $name,
        public string $sku,
        public ?string $description,
        public ?float $purchasePrice,
        public ?float $retailPrice,
        public int $quantity,
        public int $minStock,
        public string $unit,
        public ?string $supplier,
        public ?string $manufacturer,
        public ?string $model,
        public bool $isActive = true,
        public bool $isDeleted = false,
        public ?\DateTime $createdAt = null,
        public ?\DateTime $updatedAt = null,
    ) {}

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWarehouseId(): int
    {
        return $this->warehouseId;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPurchasePrice(): ?float
    {
        return $this->purchasePrice;
    }

    public function getRetailPrice(): ?float
    {
        return $this->retailPrice;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getMinStock(): int
    {
        return $this->minStock;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function getSupplier(): ?string
    {
        return $this->supplier;
    }

    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    // Business methods
    public function isOperational(): bool
    {
        return $this->isActive && !$this->isDeleted;
    }

    public function canBeSold(): bool
    {
        return $this->isOperational() && $this->quantity > 0;
    }

    public function canReceiveStock(): bool
    {
        return $this->isOperational();
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->minStock;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    public function hasStock(int $amount): bool
    {
        return $this->quantity >= $amount;
    }

    public function getTotalValue(): float
    {
        return $this->quantity * ($this->purchasePrice ?? 0);
    }

    public function getRetailValue(): float
    {
        return $this->quantity * ($this->retailPrice ?? 0);
    }

    public function getProfitMargin(): float
    {
        if (!$this->purchasePrice || !$this->retailPrice) {
            return 0;
        }

        return (($this->retailPrice - $this->purchasePrice) / $this->purchasePrice) * 100;
    }

    public function getDisplayName(): string
    {
        return $this->name . ' (' . $this->sku . ')';
    }

    public function getQuantityWithUnit(): string
    {
        return $this->quantity . ' ' . $this->unit;
    }

    public function isValidSku(): bool
    {
        return !empty($this->sku) && strlen($this->sku) <= 100;
    }

    public function hasValidPrices(): bool
    {
        return ($this->purchasePrice === null || $this->purchasePrice >= 0) &&
            ($this->retailPrice === null || $this->retailPrice >= 0);
    }
}
