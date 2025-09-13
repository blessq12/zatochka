<?php

namespace App\Application\UseCases\Bonus;

use App\Domain\Bonus\DTO\CreateBonusAccountDTO;
use App\Domain\Bonus\Entity\BonusAccount;

class CreateBonusAccountUseCase extends BaseBonusUseCase
{
    private ?CreateBonusAccountDTO $dto = null;

    public function validateSpecificData(): self
    {
        $this->dto = CreateBonusAccountDTO::fromArray($this->data);

        if (!$this->dto->clientId) {
            throw new \InvalidArgumentException('Client ID is required');
        }

        if ($this->bonusAccountRepository->existsByClientId($this->dto->clientId)) {
            throw new \InvalidArgumentException('Bonus account already exists for this client');
        }

        return $this;
    }

    public function execute(): BonusAccount
    {
        return $this->bonusAccountRepository->create($this->dto->clientId);
    }
}
