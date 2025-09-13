<?php

namespace App\Application\UseCases\Warehouse;

use App\Domain\Warehouse\AggregateRoot\WarehouseAggregateRoot;
use App\Domain\Warehouse\Entity\Warehouse;

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

    public function execute(): Warehouse
    {
        // Создаем через Event Sourcing
        $aggregate = WarehouseAggregateRoot::create();
        $aggregate->createWarehouse(
            warehouseId: 0, // Будет установлен после сохранения
            branchId: $this->data['branch_id'],
            name: $this->data['name'],
            description: $this->data['description'] ?? null,
            createdBy: auth()->id() ?? 1
        );

        $aggregate->persist();

        // Возвращаем созданную сущность
        return $this->warehouseRepository->create([
            'branch_id' => $this->data['branch_id'],
            'name' => $this->data['name'],
            'description' => $this->data['description'] ?? null,
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }
}
