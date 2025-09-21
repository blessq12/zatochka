<?php

namespace App\Application\UseCases\Repair;

use App\Domain\Repair\Entity\Repair;
use App\Domain\Warehouse\Repository\StockItemRepository;
use App\Domain\Warehouse\Repository\WarehouseRepository;

class AddPartsToRepairUseCase extends BaseRepairUseCase
{
    protected function validateSpecificData(): self
    {
        // Валидация ID ремонта
        if (empty($this->data['repair_id'])) {
            throw new \InvalidArgumentException('Repair ID is required');
        }

        $repair = $this->repairRepository->get($this->data['repair_id']);
        if (!$repair) {
            throw new \InvalidArgumentException('Repair not found');
        }

        // Валидация запчастей
        if (empty($this->data['parts']) || !is_array($this->data['parts'])) {
            throw new \InvalidArgumentException('Parts data is required and must be an array');
        }

        foreach ($this->data['parts'] as $part) {
            if (empty($part['stock_item_id'])) {
                throw new \InvalidArgumentException('Stock item ID is required for each part');
            }

            if (empty($part['quantity']) || $part['quantity'] <= 0) {
                throw new \InvalidArgumentException('Valid quantity is required for each part');
            }

            if (empty($part['warehouse_id'])) {
                throw new \InvalidArgumentException('Warehouse ID is required for each part');
            }
        }

        return $this;
    }

    public function execute(): Repair
    {
        \Log::info('AddPartsToRepairUseCase executed', $this->data);

        $repair = $this->repairRepository->get($this->data['repair_id']);
        $addedParts = [];

        foreach ($this->data['parts'] as $partData) {
            // Получаем информацию о товаре и его розничную цену
            $stockItemRepository = app(StockItemRepository::class);
            $stockItem = $stockItemRepository->get($partData['stock_item_id']);

            if (!$stockItem) {
                throw new \InvalidArgumentException('Stock item not found');
            }

            // Используем розничную цену из товара
            $unitPrice = $stockItem->getRetailPrice();
            $totalAmount = $partData['quantity'] * $unitPrice;

            // Валидация наличия запчасти на складе
            $this->validateStockAvailability($partData['stock_item_id'], $partData['quantity'], $partData['warehouse_id']);

            // Уменьшаем остаток на складе
            $this->deductStock($partData['stock_item_id'], $partData['quantity']);

            // Создаем движение по складу
            $stockMovement = $this->createStockMovement($partData, $unitPrice, $totalAmount);

            $addedParts[] = [
                'stock_item_id' => $partData['stock_item_id'],
                'quantity' => $partData['quantity'],
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'description' => $partData['description'] ?? null,
                'stock_movement_id' => $stockMovement->id,
            ];
        }

        // Обновляем ремонт с новыми запчастями
        $currentParts = $repair->getPartsUsed();
        $updatedParts = array_merge($currentParts, $addedParts);

        return $this->repairRepository->update($repair, [
            'parts_used' => $updatedParts
        ]);
    }

    private function getWarehouseForOrder($order)
    {
        $warehouseRepository = app(WarehouseRepository::class);
        $branch = $order->getBranch();

        if (!$branch) {
            throw new \InvalidArgumentException('Order branch not found');
        }

        $warehouses = $warehouseRepository->findByBranch($branch->getId());
        if (empty($warehouses)) {
            throw new \InvalidArgumentException('No warehouse found for branch');
        }

        return $warehouses[0]; // Берем первый склад филиала
    }

    private function validateStockAvailability(int $stockItemId, int $quantity, int $warehouseId): void
    {
        $stockItemRepository = app(StockItemRepository::class);
        $stockItem = $stockItemRepository->get($stockItemId);

        if (!$stockItem) {
            throw new \InvalidArgumentException('Stock item not found');
        }

        // Проверяем, что товар принадлежит указанному складу
        if ($stockItem->getWarehouseId() !== $warehouseId) {
            throw new \InvalidArgumentException('Stock item does not belong to the specified warehouse');
        }

        // Проверяем остаток на складе
        $availableQuantity = $this->getAvailableQuantity($stockItemId, $warehouseId);

        if ($availableQuantity < $quantity) {
            throw new \InvalidArgumentException(
                "Insufficient stock. Available: {$availableQuantity}, Required: {$quantity}"
            );
        }
    }

    private function getAvailableQuantity(int $stockItemId, int $warehouseId): int
    {
        $stockItemRepository = app(StockItemRepository::class);
        $stockItem = $stockItemRepository->get($stockItemId);

        if (!$stockItem) {
            return 0;
        }

        // Возвращаем текущее количество на складе
        return $stockItem->getQuantity();
    }

    private function deductStock(int $stockItemId, int $quantity): void
    {
        \Log::info('Deducting stock', ['stock_item_id' => $stockItemId, 'quantity' => $quantity]);

        $stockItemRepository = app(StockItemRepository::class);
        $stockItem = $stockItemRepository->get($stockItemId);

        if (!$stockItem) {
            throw new \InvalidArgumentException('Stock item not found');
        }

        $oldQuantity = $stockItem->getQuantity();
        $newQuantity = $oldQuantity - $quantity;

        if ($newQuantity < 0) {
            throw new \InvalidArgumentException('Insufficient stock quantity');
        }

        \Log::info('Updating stock', ['old_quantity' => $oldQuantity, 'new_quantity' => $newQuantity]);

        // Обновляем товар через репозиторий
        $stockItemRepository->update($stockItem, [
            'quantity' => $newQuantity
        ]);

        \Log::info('Stock updated successfully');
    }

    private function createStockMovement(array $partData, float $unitPrice, float $totalAmount)
    {
        // Создаем движение по складу
        $movementData = [
            'stock_item_id' => $partData['stock_item_id'],
            'quantity' => $partData['quantity'],
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'description' => $partData['description'] ?? null,
            'movement_type' => 'out',
            'warehouse_id' => $partData['warehouse_id'],
            'movement_date' => new \DateTime(),
            'created_by' => \Illuminate\Support\Facades\Auth::id() ?? 5,
        ];

        // TODO: Создать через StockMovementRepository
        return (object)['id' => rand(1, 1000)]; // Заглушка
    }
}
