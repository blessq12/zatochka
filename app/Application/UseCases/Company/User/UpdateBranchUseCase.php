<?php

namespace App\Application\UseCases\Company\User;

use App\Application\UseCases\Company\BaseUserUseCase;

class UpdateUserUseCase extends BaseUserUseCase
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
