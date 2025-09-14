<?php

namespace App\Application\UseCases\Repair\EquipmentType;

use App\Application\UseCases\Repair\BaseRepairUseCase;

class CreateEquipmentTypeUseCase extends BaseRepairUseCase
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
