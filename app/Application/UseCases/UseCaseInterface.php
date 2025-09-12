<?php

namespace App\Application\UseCases;

interface UseCaseInterface
{
    public function loadData(array $data): UseCaseInterface;

    public function validate(): UseCaseInterface;

    public function execute(): mixed;
}
