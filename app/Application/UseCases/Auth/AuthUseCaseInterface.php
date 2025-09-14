<?php

namespace App\Application\UseCases\Auth;

interface AuthUseCaseInterface
{
    public function loadData(array $data): self;

    public function validate(): self;

    public function execute(): mixed;
}
