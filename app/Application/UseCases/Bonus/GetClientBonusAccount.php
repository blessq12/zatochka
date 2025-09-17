<?php

namespace App\Application\UseCases\Bonus;

class GetClientBonusAccount extends BaseBonusUseCase
{
    public function validateSpecificData(): self
    {
        return $this;
    }

    public function execute(): mixed
    {
        return $this->bonusAccountRepository->getByClientId($this->data['id']);
    }
}
