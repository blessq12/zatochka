<?php

namespace App\Application\UseCases\Company\Branch;

use App\Application\UseCases\Company\BaseBranchUseCase;

class GetBranchUseCase extends BaseBranchUseCase
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
