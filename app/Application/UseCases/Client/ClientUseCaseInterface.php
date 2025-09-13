<?php

namespace App\Application\UseCases\Client;

interface ClientUseCaseInterface
{
    public function loadData(array $data): ClientUseCaseInterface;

    public function validate(): ClientUseCaseInterface;

    public function execute(): mixed;
}
