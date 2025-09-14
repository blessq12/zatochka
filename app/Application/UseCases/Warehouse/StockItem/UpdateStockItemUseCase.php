<?php

namespace App\Application\UseCases\Warehouse\StockItem;

use App\Application\UseCases\Warehouse\BaseWarehouseUseCase;

class UpdateStockItemUseCase extends BaseWarehouseUseCase
{
    public function validateSpecificData(): self
    {
        // TODO: Add validation logic
        return $this;
    }

    public function execute(): mixed
    {
        // TODO: Implement update logic
        return $this->data;
    }
}
