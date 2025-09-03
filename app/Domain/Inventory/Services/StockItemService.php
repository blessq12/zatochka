<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Entities\StockItem;
use App\Domain\Inventory\ValueObjects\StockItemName;
use App\Domain\Inventory\ValueObjects\SKU;
use App\Domain\Inventory\ValueObjects\Quantity;
use App\Domain\Inventory\ValueObjects\Money;
use App\Domain\Inventory\ValueObjects\Unit;
use App\Domain\Inventory\Interfaces\StockItemRepositoryInterface;
use App\Domain\Shared\Events\EventBusInterface;
use InvalidArgumentException;

class StockItemService
{
    public function __construct(
        private readonly StockItemRepositoryInterface $stockItemRepository,
        private readonly EventBusInterface $eventBus
    ) {}

    public function createStockItem(
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
    ): StockItem {
        // Проверяем уникальность SKU в рамках склада
        if ($this->stockItemRepository->existsBySkuAndWarehouse($sku, $warehouseId)) {
            throw new InvalidArgumentException('SKU already exists in this warehouse');
        }

        $stockItem = StockItem::create(
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

        $this->stockItemRepository->save($stockItem);
        $this->publishEvents($stockItem);

        return $stockItem;
    }

    public function addStock(int $id, Quantity $amount): StockItem
    {
        $stockItem = $this->getStockItemOrFail($id);
        $stockItem->addQuantity($amount);
        $this->stockItemRepository->save($stockItem);
        $this->publishEvents($stockItem);
        return $stockItem;
    }

    public function subtractStock(int $id, Quantity $amount): StockItem
    {
        $stockItem = $this->getStockItemOrFail($id);
        $stockItem->subtractQuantity($amount);
        $this->stockItemRepository->save($stockItem);
        $this->publishEvents($stockItem);
        return $stockItem;
    }

    public function setStock(int $id, Quantity $newQuantity): StockItem
    {
        $stockItem = $this->getStockItemOrFail($id);
        $stockItem->setQuantity($newQuantity);
        $this->stockItemRepository->save($stockItem);
        $this->publishEvents($stockItem);
        return $stockItem;
    }

    public function updatePrices(int $id, ?Money $purchasePrice, ?Money $retailPrice): StockItem
    {
        $stockItem = $this->getStockItemOrFail($id);
        $stockItem->updatePrices($purchasePrice, $retailPrice);
        $this->stockItemRepository->save($stockItem);
        $this->publishEvents($stockItem);
        return $stockItem;
    }

    public function updateMinStock(int $id, Quantity $newMinStock): StockItem
    {
        $stockItem = $this->getStockItemOrFail($id);
        $stockItem->updateMinStock($newMinStock);
        $this->stockItemRepository->save($stockItem);
        $this->publishEvents($stockItem);
        return $stockItem;
    }

    public function activateStockItem(int $id): StockItem
    {
        $stockItem = $this->getStockItemOrFail($id);
        $stockItem->activate();
        $this->stockItemRepository->save($stockItem);
        $this->publishEvents($stockItem);
        return $stockItem;
    }

    public function deactivateStockItem(int $id): StockItem
    {
        $stockItem = $this->getStockItemOrFail($id);
        $stockItem->deactivate();
        $this->stockItemRepository->save($stockItem);
        $this->publishEvents($stockItem);
        return $stockItem;
    }

    public function deleteStockItem(int $id): void
    {
        $stockItem = $this->getStockItemOrFail($id);

        if (!$stockItem->canBeDeleted()) {
            throw new InvalidArgumentException('Stock item cannot be deleted');
        }

        $stockItem->markDeleted();
        $this->stockItemRepository->save($stockItem);
        $this->publishEvents($stockItem);
    }

    public function getStockItem(int $id): ?StockItem
    {
        return $this->stockItemRepository->findById($id);
    }

    public function getStockItemBySku(SKU $sku): ?StockItem
    {
        return $this->stockItemRepository->findBySku($sku);
    }

    public function getStockItemsByWarehouse(int $warehouseId): array
    {
        return $this->stockItemRepository->findByWarehouseId($warehouseId);
    }

    public function getStockItemsByCategory(int $categoryId): array
    {
        return $this->stockItemRepository->findByCategoryId($categoryId);
    }

    public function getLowStockItems(): array
    {
        return $this->stockItemRepository->findLowStock();
    }

    public function getOutOfStockItems(): array
    {
        return $this->stockItemRepository->findOutOfStock();
    }

    public function getAllStockItems(): array
    {
        return $this->stockItemRepository->findAll();
    }

    public function getActiveStockItems(): array
    {
        return $this->stockItemRepository->findActive();
    }

    private function getStockItemOrFail(int $id): StockItem
    {
        $stockItem = $this->stockItemRepository->findById($id);
        if (!$stockItem) {
            throw new InvalidArgumentException('Stock item not found');
        }
        return $stockItem;
    }

    private function publishEvents(StockItem $stockItem): void
    {
        while ($stockItem->hasEvents()) {
            $events = $stockItem->pullEvents();
            foreach ($events as $event) {
                $this->eventBus->publish($event);
            }
        }
    }
}
