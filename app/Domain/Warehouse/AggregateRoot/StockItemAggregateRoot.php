<?php

namespace App\Domain\Warehouse\AggregateRoot;

use App\Domain\Warehouse\Event\StockItemCreated;
use App\Domain\Warehouse\Event\StockItemUpdated;
use App\Domain\Warehouse\Event\StockItemStockAdjusted;
use App\Domain\Warehouse\Event\StockItemActivated;
use App\Domain\Warehouse\Event\StockItemDeactivated;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Illuminate\Support\Str;

class StockItemAggregateRoot extends AggregateRoot
{
    public function createStockItem(
        int $stockItemId,
        int $warehouseId,
        int $categoryId,
        string $name,
        string $sku,
        ?string $description,
        ?float $purchasePrice,
        ?float $retailPrice,
        int $quantity,
        int $minStock,
        string $unit,
        ?string $supplier,
        ?string $manufacturer,
        ?string $model,
        int $createdBy
    ): self {
        $this->recordThat(new StockItemCreated(
            stockItemId: $stockItemId,
            warehouseId: $warehouseId,
            categoryId: $categoryId,
            name: $name,
            sku: $sku,
            description: $description,
            purchasePrice: $purchasePrice,
            retailPrice: $retailPrice,
            quantity: $quantity,
            minStock: $minStock,
            unit: $unit,
            supplier: $supplier,
            manufacturer: $manufacturer,
            model: $model,
            createdBy: $createdBy
        ));

        return $this;
    }

    public function updateStockItem(
        int $stockItemId,
        string $name,
        string $sku,
        ?string $description,
        ?float $purchasePrice,
        ?float $retailPrice,
        int $minStock,
        string $unit,
        ?string $supplier,
        ?string $manufacturer,
        ?string $model,
        int $updatedBy
    ): self {
        $this->recordThat(new StockItemUpdated(
            stockItemId: $stockItemId,
            name: $name,
            sku: $sku,
            description: $description,
            purchasePrice: $purchasePrice,
            retailPrice: $retailPrice,
            minStock: $minStock,
            unit: $unit,
            supplier: $supplier,
            manufacturer: $manufacturer,
            model: $model,
            updatedBy: $updatedBy
        ));

        return $this;
    }

    public function adjustStock(
        int $stockItemId,
        int $previousQuantity,
        int $newQuantity,
        int $adjustmentQuantity,
        string $reason,
        ?int $userId
    ): self {
        $this->recordThat(new StockItemStockAdjusted(
            stockItemId: $stockItemId,
            previousQuantity: $previousQuantity,
            newQuantity: $newQuantity,
            adjustmentQuantity: $adjustmentQuantity,
            reason: $reason,
            userId: $userId
        ));

        return $this;
    }

    public function activateStockItem(int $stockItemId, int $activatedBy): self
    {
        $this->recordThat(new StockItemActivated(
            stockItemId: $stockItemId,
            activatedBy: $activatedBy
        ));

        return $this;
    }

    public function deactivateStockItem(int $stockItemId, int $deactivatedBy): self
    {
        $this->recordThat(new StockItemDeactivated(
            stockItemId: $stockItemId,
            deactivatedBy: $deactivatedBy
        ));

        return $this;
    }

    public static function create(): self
    {
        return static::retrieve(Str::uuid()->toString());
    }
}
