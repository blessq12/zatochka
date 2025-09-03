<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Inventory\ValueObjects\StockItemName;
use App\Domain\Inventory\ValueObjects\SKU;
use App\Domain\Inventory\ValueObjects\Quantity;
use App\Domain\Inventory\ValueObjects\Money;

class StockItemCreated extends DomainEvent
{
    public function __construct(
        private readonly int $stockItemId,
        private readonly int $warehouseId,
        private readonly int $categoryId,
        private readonly StockItemName $name,
        private readonly SKU $sku,
        private readonly ?Money $purchasePrice,
        private readonly ?Money $retailPrice,
        private readonly Quantity $quantity,
        private readonly Quantity $minStock
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'StockItemCreated';
    }

    public function eventData(): array
    {
        return [
            'stock_item_id' => $this->stockItemId,
            'warehouse_id' => $this->warehouseId,
            'category_id' => $this->categoryId,
            'name' => (string) $this->name,
            'sku' => (string) $this->sku,
            'purchase_price' => $this->purchasePrice ? (string) $this->purchasePrice : null,
            'retail_price' => $this->retailPrice ? (string) $this->retailPrice : null,
            'quantity' => $this->quantity->value(),
            'min_stock' => $this->minStock->value(),
        ];
    }

    public function stockItemId(): int
    {
        return $this->stockItemId;
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
}
