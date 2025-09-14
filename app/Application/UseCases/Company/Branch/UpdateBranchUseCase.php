<?php

namespace App\Application\UseCases\Company\Branch;

use App\Application\UseCases\Company\BaseBranchUseCase;

class UpdateBranchUseCase extends BaseBranchUseCase
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
