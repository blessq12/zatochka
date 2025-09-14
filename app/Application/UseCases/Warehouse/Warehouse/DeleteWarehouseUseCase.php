<?php

namespace App\Application\UseCases\Warehouse\Warehouse;

use App\Application\UseCases\Warehouse\BaseWarehouseUseCase;

class DeleteWarehouseUseCase extends BaseWarehouseUseCase
{
    public function validateSpecificData(): self
    {
        if (empty($this->data['id'])) {
            throw new \InvalidArgumentException('ID склада обязателен');
        }

        if (!is_numeric($this->data['id'])) {
            throw new \InvalidArgumentException('ID склада должен быть числом');
        }

        // Проверяем существование склада
        if (!$this->warehouseRepository->exists($this->data['id'])) {
            throw new \InvalidArgumentException('Склад не найден');
        }

        return $this;
    }

    public function execute(): mixed
    {
        $warehouse = $this->warehouseRepository->get($this->data['id']);

        if (!$warehouse) {
            throw new \InvalidArgumentException('Склад не найден');
        }

        // Проверяем есть ли товары на складе
        $stockItemsCount = $this->stockItemRepository->countByWarehouse($this->data['id']);
        if ($stockItemsCount > 0) {
            throw new \InvalidArgumentException('Нельзя удалить склад с товарами. Сначала переместите все товары');
        }

        // Soft delete
        return $this->warehouseRepository->delete($warehouse->getId());
    }
}
