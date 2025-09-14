<?php

namespace App\Application\UseCases\Repair\Repair;

use App\Application\UseCases\Repair\BaseRepairUseCase;

class GetRepairUseCase extends BaseRepairUseCase
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
