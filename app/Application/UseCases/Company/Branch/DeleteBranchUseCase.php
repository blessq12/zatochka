<?php

namespace App\Application\UseCases\Company\Branch;

use App\Application\UseCases\Company\BaseBranchUseCase;

class DeleteBranchUseCase extends BaseBranchUseCase
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
