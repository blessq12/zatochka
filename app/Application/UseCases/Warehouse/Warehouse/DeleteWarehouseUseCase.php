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

        // Проверяем есть ли категории на складе
        $categoriesCount = $this->stockCategoryRepository->countByWarehouse($this->data['id']);
        if ($categoriesCount > 0) {
            throw new \InvalidArgumentException("Нельзя удалить склад с категориями товаров. На складе есть {$categoriesCount} категорий. При удалении склада будут удалены все категории и товары. Сначала переместите все товары в другие склады.");
        }

        // Проверяем есть ли товары на складе (дополнительная проверка)
        $stockItemsCount = $this->stockItemRepository->countByWarehouse($this->data['id']);
        if ($stockItemsCount > 0) {
            throw new \InvalidArgumentException("Нельзя удалить склад с товарами. На складе есть {$stockItemsCount} товаров. При удалении склада будут удалены все товары. Сначала переместите все товары в другие склады.");
        }

        // Hard delete
        return $this->warehouseRepository->delete($warehouse->getId());
    }
}
