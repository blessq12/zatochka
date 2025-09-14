<?php

namespace App\Application\UseCases\Warehouse\StockCategory;

use App\Application\UseCases\Warehouse\BaseWarehouseUseCase;

class DeleteStockCategoryUseCase extends BaseWarehouseUseCase
{
    public function validateSpecificData(): self
    {
        if (empty($this->data['id'])) {
            throw new \InvalidArgumentException('ID категории обязателен');
        }

        if (!is_numeric($this->data['id'])) {
            throw new \InvalidArgumentException('ID категории должен быть числом');
        }

        // Проверяем существование категории
        if (!$this->stockCategoryRepository->exists($this->data['id'])) {
            throw new \InvalidArgumentException('Категория не найдена');
        }

        return $this;
    }

    public function execute(): mixed
    {
        $category = $this->stockCategoryRepository->get($this->data['id']);

        if (!$category) {
            throw new \InvalidArgumentException('Категория не найдена');
        }

        $itemsCount = $this->stockItemRepository->countByCategory($this->data['id']);
        if ($itemsCount > 0) {
            throw new \InvalidArgumentException("Нельзя удалить категорию с товарами. В категории есть {$itemsCount} товаров. Сначала переместите все товары в другую категорию.");
        }

        $this->stockCategoryRepository->delete($category->getId());
        return true;
    }
}
