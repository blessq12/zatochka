<?php

namespace App\Application\UseCases\Bonus;

interface BonusUseCaseInterface
{
    public function loadData(array $data): BonusUseCaseInterface;

    public function validate(): BonusUseCaseInterface;

    public function execute(): mixed;
}
