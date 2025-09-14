<?php

namespace App\Application\UseCases\Warehouse\StockCategory;

use App\Application\UseCases\Warehouse\BaseWarehouseUseCase;

class DeleteStockCategoryUseCase extends BaseWarehouseUseCase
{
    public function validateSpecificData(): self
    {
        // TODO: Add validation logic
        return $this;
    }

    public function execute(): mixed
    {
        // TODO: Implement delete logic
        return $this->data;
    }
}
