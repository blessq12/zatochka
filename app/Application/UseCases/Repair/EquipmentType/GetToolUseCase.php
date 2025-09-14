<?php

namespace App\Application\UseCases\Repair\EquipmentType;

use App\Application\UseCases\Repair\BaseRepairUseCase;

class GetEquipmentTypeUseCase extends BaseRepairUseCase
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
