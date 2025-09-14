<?php

namespace App\Application\UseCases\Warehouse\StockCategory;

use App\Application\UseCases\Warehouse\BaseWarehouseUseCase;

class CreateStockCategoryUseCase extends BaseWarehouseUseCase
{
    public function validateSpecificData(): self
    {
        if (empty($this->data['name'])) {
            throw new \InvalidArgumentException('Название категории обязательно');
        }

        if (empty($this->data['color'])) {
            $this->data['color'] = '#6B7280'; // Серый цвет по умолчанию
        }

        if (empty($this->data['sort_order'])) {
            $this->data['sort_order'] = 0;
        }

        if (empty($this->data['warehouse_id'])) {
            throw new \InvalidArgumentException('Склад обязателен');
        }

        // Проверяем уникальность названия в рамках склада
        if ($this->stockCategoryRepository->existsByNameInWarehouse($this->data['name'], $this->data['warehouse_id'])) {
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
        $categoryData = [
            'warehouse_id' => $this->data['warehouse_id'],
            'name' => $this->data['name'],
            'description' => $this->data['description'] ?? null,
            'color' => $this->data['color'],
            'sort_order' => $this->data['sort_order'],
            'is_active' => $this->data['is_active'] ?? true,
            'is_deleted' => false,
        ];

        return $this->stockCategoryRepository->create($categoryData);
    }
}
