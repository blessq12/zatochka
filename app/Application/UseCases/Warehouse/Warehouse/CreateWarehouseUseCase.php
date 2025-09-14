<?php

namespace App\Application\UseCases\Warehouse\Warehouse;

use App\Application\UseCases\Warehouse\BaseWarehouseUseCase;

class CreateWarehouseUseCase extends BaseWarehouseUseCase
{
    public function validateSpecificData(): self
    {
        if (empty($this->data['name'])) {
            throw new \InvalidArgumentException('Название склада обязательно');
        }

        if (empty($this->data['branch_id'])) {
            throw new \InvalidArgumentException('Филиал обязателен');
        }

        // Проверяем уникальность названия в рамках филиала
        if ($this->warehouseRepository->existsByNameInBranch(
            $this->data['name'],
            $this->data['branch_id']
        )) {
            throw new \InvalidArgumentException('Склад с таким названием уже существует в данном филиале');
        }

        return $this;
    }

    public function execute(): mixed
    {
        $warehouseData = [
            'branch_id' => $this->data['branch_id'],
            'name' => $this->data['name'],
            'description' => $this->data['description'] ?? null,
            'is_active' => $this->data['is_active'] ?? true,
            'is_deleted' => false,
        ];

        return $this->warehouseRepository->create($warehouseData);
    }
}
