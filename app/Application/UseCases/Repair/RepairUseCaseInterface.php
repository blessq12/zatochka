<?php

namespace App\Application\UseCases\Repair;

interface RepairUseCaseInterface
{
    public function loadData(array $data): self;
    public function validate(): self;
    public function execute(): mixed;
}
