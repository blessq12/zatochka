<?php

namespace App\Application\UseCases\Warehouse\StockItem;

use App\Application\UseCases\Warehouse\BaseWarehouseUseCase;

class DeleteStockItemUseCase extends BaseWarehouseUseCase
{
    public function validateSpecificData(): self
    {
        if (empty($this->data['id'])) {
            throw new \InvalidArgumentException('ID товара обязателен');
        }

        if (!is_numeric($this->data['id'])) {
            throw new \InvalidArgumentException('ID товара должен быть числом');
        }

        // Проверяем существование товара
        if (!$this->stockItemRepository->exists($this->data['id'])) {
            throw new \InvalidArgumentException('Товар не найден');
        }

        return $this;
    }

    public function execute(): mixed
    {
        $item = $this->stockItemRepository->get($this->data['id']);
        if (!$item) {
            throw new \InvalidArgumentException('Товар не найден');
        }

        // Hard delete
        $this->stockItemRepository->delete($item->getId());
        return true;
    }
}
