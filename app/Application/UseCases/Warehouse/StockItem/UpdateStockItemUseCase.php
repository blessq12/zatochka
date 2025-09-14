<?php

namespace App\Application\UseCases\Warehouse\StockItem;

use App\Application\UseCases\Warehouse\BaseWarehouseUseCase;

class UpdateStockItemUseCase extends BaseWarehouseUseCase
{
    public function validateSpecificData(): self
    {
        if (empty($this->data['id'])) {
            throw new \InvalidArgumentException('ID товара обязателен');
        }

        if (!is_numeric($this->data['id'])) {
            throw new \InvalidArgumentException('ID товара должен быть числом');
        }

        if (empty($this->data['name'])) {
            throw new \InvalidArgumentException('Название товара обязательно');
        }

        if (empty($this->data['sku'])) {
            throw new \InvalidArgumentException('SKU товара обязателен');
        }

        if (empty($this->data['category_id'])) {
            throw new \InvalidArgumentException('Категория обязательна');
        }

        if (!isset($this->data['quantity']) || !is_numeric($this->data['quantity'])) {
            throw new \InvalidArgumentException('Количество товара должно быть числом');
        }

        if (!isset($this->data['min_stock']) || !is_numeric($this->data['min_stock'])) {
            throw new \InvalidArgumentException('Минимальный остаток должен быть числом');
        }

        if (empty($this->data['unit'])) {
            $this->data['unit'] = 'шт';
        }

        // Проверяем существование товара
        if (!$this->stockItemRepository->exists($this->data['id'])) {
            throw new \InvalidArgumentException('Товар не найден');
        }

        // Проверяем уникальность SKU (исключая текущий товар)
        if ($this->stockItemRepository->existsBySku($this->data['sku'], $this->data['id'])) {
            throw new \InvalidArgumentException('Товар с таким SKU уже существует');
        }

        // Проверяем существование категории и получаем склад из неё
        $category = $this->stockCategoryRepository->get($this->data['category_id']);
        if (!$category) {
            throw new \InvalidArgumentException('Выбранная категория не найдена');
        }

        // Автоматически определяем склад из категории
        $this->data['warehouse_id'] = $category->getWarehouseId();

        return $this;
    }

    public function execute(): mixed
    {
        $item = $this->stockItemRepository->get($this->data['id']);
        if (!$item) {
            throw new \InvalidArgumentException('Товар не найден');
        }

        $updateData = [
            'warehouse_id' => $this->data['warehouse_id'],
            'category_id' => $this->data['category_id'],
            'name' => $this->data['name'],
            'sku' => $this->data['sku'],
            'description' => $this->data['description'] ?? null,
            'purchase_price' => $this->data['purchase_price'] ?? null,
            'retail_price' => $this->data['retail_price'] ?? null,
            'quantity' => (int)$this->data['quantity'],
            'min_stock' => (int)$this->data['min_stock'],
            'unit' => $this->data['unit'],
            'supplier' => $this->data['supplier'] ?? null,
            'manufacturer' => $this->data['manufacturer'] ?? null,
            'model' => $this->data['model'] ?? null,
            'is_active' => $this->data['is_active'] ?? true,
        ];

        return $this->stockItemRepository->update($item, $updateData);
    }
}
