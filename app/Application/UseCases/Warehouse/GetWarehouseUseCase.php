<?php

namespace App\Application\UseCases\Warehouse;

use App\Domain\Warehouse\Entity\Warehouse;

class GetWarehouseUseCase extends BaseWarehouseUseCase
{
    public function validateSpecificData(): self
    {
        if (empty($this->data['id'])) {
            throw new \InvalidArgumentException('ID склада обязателен');
        }

        if (!is_numeric($this->data['id'])) {
            throw new \InvalidArgumentException('ID склада должен быть числом');
        }

        return $this;
    }

    public function execute(): ?Warehouse
    {
        return $this->warehouseRepository->get($this->data['id']);
    }
}
