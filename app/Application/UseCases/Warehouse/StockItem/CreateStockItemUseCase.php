<?php

namespace App\Application\UseCases\Warehouse\StockItem;

use App\Application\UseCases\Warehouse\BaseWarehouseUseCase;

class CreateStockItemUseCase extends BaseWarehouseUseCase
{
    public function validateSpecificData(): self
    {
        // TODO: Add validation logic
        return $this;
    }

    public function execute(): mixed
    {
        // TODO: Implement create logic
        return $this->data;
    }
}
