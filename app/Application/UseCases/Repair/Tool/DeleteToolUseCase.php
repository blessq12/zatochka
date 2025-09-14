<?php

namespace App\Application\UseCases\Repair\Tool;

use App\Application\UseCases\Repair\BaseRepairUseCase;

class DeleteToolUseCase extends BaseRepairUseCase
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
