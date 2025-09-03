<?php

namespace App\Domain\Inventory\Entities;

use App\Domain\Inventory\ValueObjects\StockItemName;
use App\Domain\Inventory\ValueObjects\SKU;
use App\Domain\Inventory\ValueObjects\Quantity;
use App\Domain\Inventory\ValueObjects\Money;
use App\Domain\Inventory\ValueObjects\Unit;
use App\Domain\Shared\Interfaces\AggregateRoot;
use App\Domain\Inventory\Events\StockItemCreated;
use App\Domain\Inventory\Events\StockItemUpdated;
use App\Domain\Inventory\Events\StockItemQuantityChanged;
use App\Domain\Inventory\Events\StockItemDeactivated;

class StockItem implements AggregateRoot
{
    private int $id;
    private int $warehouseId;
    private int $categoryId;
    private StockItemName $name;
    private SKU $sku;
    private ?string $description;
    private ?Money $purchasePrice;
    private ?Money $retailPrice;
    private Quantity $quantity;
    private Quantity $minStock;
    private Unit $unit;
    private ?string $supplier;
    private ?string $manufacturer;
    private ?string $model;
    private bool $isActive;
    private bool $isDeleted;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        int $id,
        int $warehouseId,
        int $categoryId,
        StockItemName $name,
        SKU $sku,
        ?string $description = null,
        ?Money $purchasePrice = null,
        ?Money $retailPrice = null,
        Quantity $quantity = null,
        Quantity $minStock = null,
        Unit $unit = null,
        ?string $supplier = null,
        ?string $manufacturer = null,
        ?string $model = null
    ) {
        $this->id = $id;
        $this->warehouseId = $warehouseId;
        $this->categoryId = $categoryId;
        $this->name = $name;
        $this->sku = $sku;
        $this->description = $description;
        $this->purchasePrice = $purchasePrice;
        $this->retailPrice = $retailPrice;
        $this->quantity = $quantity ?? Quantity::zero();
        $this->minStock = $minStock ?? Quantity::zero();
        $this->unit = $unit ?? Unit::fromString('шт');
        $this->supplier = $supplier;
        $this->manufacturer = $manufacturer;
        $this->model = $model;
        $this->isActive = true;
        $this->isDeleted = false;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        int $id,
        int $warehouseId,
        int $categoryId,
        StockItemName $name,
        SKU $sku,
        ?string $description = null,
        ?Money $purchasePrice = null,
        ?Money $retailPrice = null,
        Quantity $quantity = null,
        Quantity $minStock = null,
        Unit $unit = null,
        ?string $supplier = null,
        ?string $manufacturer = null,
        ?string $model = null
    ): self {
        $stockItem = new self(
            $id,
            $warehouseId,
            $categoryId,
            $name,
            $sku,
            $description,
            $purchasePrice,
            $retailPrice,
            $quantity,
            $minStock,
            $unit,
            $supplier,
            $manufacturer,
            $model
        );

        $stockItem->recordEvent(new StockItemCreated(
            $stockItem->id(),
            $stockItem->warehouseId(),
            $stockItem->categoryId(),
            $stockItem->name(),
            $stockItem->sku(),
            $stockItem->purchasePrice(),
            $stockItem->retailPrice(),
            $stockItem->quantity(),
            $stockItem->minStock()
        ));

        return $stockItem;
    }

    public static function reconstitute(
        int $id,
        int $warehouseId,
        int $categoryId,
        StockItemName $name,
        SKU $sku,
        ?string $description,
        ?Money $purchasePrice,
        ?Money $retailPrice,
        Quantity $quantity,
        Quantity $minStock,
        Unit $unit,
        ?string $supplier,
        ?string $manufacturer,
        ?string $model,
        bool $isActive,
        bool $isDeleted,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ): self {
        $stockItem = new self(
            $id,
            $warehouseId,
            $categoryId,
            $name,
            $sku,
            $description,
            $purchasePrice,
            $retailPrice,
            $quantity,
            $minStock,
            $unit,
            $supplier,
            $manufacturer,
            $model
        );

        $stockItem->isActive = $isActive;
        $stockItem->isDeleted = $isDeleted;
        $stockItem->createdAt = $createdAt;
        $stockItem->updatedAt = $updatedAt;

        return $stockItem;
    }

    // Getters
    public function id(): int
    {
        return $this->id;
    }
    public function warehouseId(): int
    {
        return $this->warehouseId;
    }
    public function categoryId(): int
    {
        return $this->categoryId;
    }
    public function name(): StockItemName
    {
        return $this->name;
    }
    public function sku(): SKU
    {
        return $this->sku;
    }
    public function description(): ?string
    {
        return $this->description;
    }
    public function purchasePrice(): ?Money
    {
        return $this->purchasePrice;
    }
    public function retailPrice(): ?Money
    {
        return $this->retailPrice;
    }
    public function quantity(): Quantity
    {
        return $this->quantity;
    }
    public function minStock(): Quantity
    {
        return $this->minStock;
    }
    public function unit(): Unit
    {
        return $this->unit;
    }
    public function supplier(): ?string
    {
        return $this->supplier;
    }
    public function manufacturer(): ?string
    {
        return $this->manufacturer;
    }
    public function model(): ?string
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
    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Business methods
    public function addQuantity(Quantity $amount): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot modify deleted stock item');
        }
        if ($amount->value() <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        $oldQuantity = $this->quantity;
        $this->quantity = $this->quantity->add($amount);
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new StockItemQuantityChanged(
            $this->id,
            $oldQuantity,
            $this->quantity,
            'added'
        ));
    }

    public function subtractQuantity(Quantity $amount): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot modify deleted stock item');
        }
        if ($amount->value() <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }
        if ($this->quantity->value() < $amount->value()) {
            throw new \InvalidArgumentException('Insufficient stock');
        }

        $oldQuantity = $this->quantity;
        $this->quantity = $this->quantity->subtract($amount);
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new StockItemQuantityChanged(
            $this->id,
            $oldQuantity,
            $this->quantity,
            'subtracted'
        ));
    }

    public function setQuantity(Quantity $newQuantity): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot modify deleted stock item');
        }
        if ($newQuantity->value() < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative');
        }

        $oldQuantity = $this->quantity;
        $this->quantity = $newQuantity;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new StockItemQuantityChanged(
            $this->id,
            $oldQuantity,
            $this->quantity,
            'set'
        ));
    }

    public function updatePrices(?Money $purchasePrice, ?Money $retailPrice): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot modify deleted stock item');
        }

        $this->purchasePrice = $purchasePrice;
        $this->retailPrice = $retailPrice;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new StockItemUpdated($this->id, 'prices'));
    }

    public function updateMinStock(Quantity $newMinStock): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot modify deleted stock item');
        }
        if ($newMinStock->value() < 0) {
            throw new \InvalidArgumentException('Min stock cannot be negative');
        }

        $this->minStock = $newMinStock;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new StockItemUpdated($this->id, 'min_stock'));
    }

    public function deactivate(): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot deactivate deleted stock item');
        }
        if (!$this->isActive) {
            return;
        }

        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new StockItemDeactivated($this->id));
    }

    public function activate(): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot activate deleted stock item');
        }
        if ($this->isActive) {
            return;
        }

        $this->isActive = true;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markDeleted(): void
    {
        if ($this->isDeleted) {
            return;
        }
        $this->isDeleted = true;
        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isLowStock(): bool
    {
        return $this->quantity->value() <= $this->minStock->value();
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity->value() <= 0;
    }

    public function canBeDeleted(): bool
    {
        // Логика проверки возможности удаления
        return $this->quantity->value() == 0;
    }

    // Event handling
    private array $events = [];
    protected function recordEvent(object $event): void
    {
        $this->events[] = $event;
    }
    public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
    public function hasEvents(): bool
    {
        return !empty($this->events);
    }
}
