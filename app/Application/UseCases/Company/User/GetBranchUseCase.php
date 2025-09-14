<?php

namespace App\Application\UseCases\Company\User;

use App\Application\UseCases\Company\BaseUserUseCase;

class GetUserUseCase extends BaseUserUseCase
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
