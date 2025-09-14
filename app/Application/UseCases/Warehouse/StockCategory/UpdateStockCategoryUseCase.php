<?php

namespace App\Application\UseCases\Warehouse\StockCategory;

use App\Application\UseCases\Warehouse\BaseWarehouseUseCase;

class UpdateStockCategoryUseCase extends BaseWarehouseUseCase
{
    public function validateSpecificData(): self
    {
        if (empty($this->data['id'])) {
            throw new \InvalidArgumentException('ID категории обязателен');
        }

        if (!is_numeric($this->data['id'])) {
            throw new \InvalidArgumentException('ID категории должен быть числом');
        }

        if (empty($this->data['name'])) {
            throw new \InvalidArgumentException('Название категории обязательно');
        }

        if (empty($this->data['color'])) {
            $this->data['color'] = '#6B7280';
        }

        if (empty($this->data['sort_order'])) {
            $this->data['sort_order'] = 0;
        }

        // Проверяем существование категории
        if (!$this->stockCategoryRepository->exists($this->data['id'])) {
            throw new \InvalidArgumentException('Категория не найдена');
        }

        if (empty($this->data['warehouse_id'])) {
            throw new \InvalidArgumentException('Склад обязателен');
        }

        // Проверяем уникальность названия в рамках склада (исключая текущую)
        if ($this->stockCategoryRepository->existsByNameInWarehouse($this->data['name'], $this->data['warehouse_id'], $this->data['id'])) {
            throw new \InvalidArgumentException('Категория с таким названием уже существует в данном складе');
        }

        // Валидируем цвет
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $this->data['color'])) {
            throw new \InvalidArgumentException('Неверный формат цвета (должен быть #RRGGBB)');
        }

        return $this;
    }

    public function execute(): mixed
    {
        $category = $this->stockCategoryRepository->get($this->data['id']);

        if (!$category) {
            throw new \InvalidArgumentException('Категория не найдена');
        }

        $updateData = [
            'warehouse_id' => $this->data['warehouse_id'],
            'name' => $this->data['name'],
            'description' => $this->data['description'] ?? null,
            'color' => $this->data['color'],
            'sort_order' => $this->data['sort_order'],
            'is_active' => $this->data['is_active'] ?? true,
        ];

        return $this->stockCategoryRepository->update($category, $updateData);
    }
}
