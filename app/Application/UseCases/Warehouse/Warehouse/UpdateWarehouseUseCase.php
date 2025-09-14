<?php

namespace App\Application\UseCases\Warehouse\Warehouse;

use App\Application\UseCases\Warehouse\BaseWarehouseUseCase;

class UpdateWarehouseUseCase extends BaseWarehouseUseCase
{
    public function validateSpecificData(): self
    {
        if (empty($this->data['id'])) {
            throw new \InvalidArgumentException('ID склада обязателен');
        }

        if (!is_numeric($this->data['id'])) {
            throw new \InvalidArgumentException('ID склада должен быть числом');
        }

        if (empty($this->data['name'])) {
            throw new \InvalidArgumentException('Название склада обязательно');
        }

        if (empty($this->data['branch_id'])) {
            throw new \InvalidArgumentException('Филиал обязателен');
        }

        // Проверяем существование склада
        if (!$this->warehouseRepository->exists($this->data['id'])) {
            throw new \InvalidArgumentException('Склад не найден');
        }

        // Проверяем уникальность названия (исключая текущий склад)
        if ($this->warehouseRepository->existsByNameInBranch(
            $this->data['name'],
            $this->data['branch_id'],
            $this->data['id']
        )) {
            throw new \InvalidArgumentException('Склад с таким названием уже существует в данном филиале');
        }

        return $this;
    }

    public function execute(): mixed
    {
        $warehouse = $this->warehouseRepository->get($this->data['id']);

        if (!$warehouse) {
            throw new \InvalidArgumentException('Склад не найден');
        }

        $updateData = [
            'branch_id' => $this->data['branch_id'],
            'name' => $this->data['name'],
            'description' => $this->data['description'] ?? null,
            'is_active' => $this->data['is_active'] ?? true,
        ];

        return $this->warehouseRepository->update($warehouse, $updateData);
    }
}
