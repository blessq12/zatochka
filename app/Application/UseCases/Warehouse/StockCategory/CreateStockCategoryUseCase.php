<?php

namespace App\Application\UseCases\Warehouse\StockCategory;

use App\Application\UseCases\Warehouse\BaseWarehouseUseCase;

class CreateStockCategoryUseCase extends BaseWarehouseUseCase
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
