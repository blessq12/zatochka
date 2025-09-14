<?php

namespace App\Application\UseCases\Warehouse\Warehouse;

use App\Application\UseCases\Warehouse\BaseWarehouseUseCase;

class GetWarehouseUseCase extends BaseWarehouseUseCase
{
    public function validateSpecificData(): self
    {
        // TODO: Add validation logic
        return $this;
    }

    public function execute(): mixed
    {
        // TODO: Implement get logic
        return $this->data;
    }
}
