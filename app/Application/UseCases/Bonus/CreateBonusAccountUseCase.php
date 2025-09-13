<?php

namespace App\Application\UseCases\Bonus;

use App\Domain\Bonus\DTO\CreateBonusAccountDTO;
use App\Domain\Bonus\Entity\BonusAccount;
use App\Domain\Bonus\AggregateRoot\BonusAccountAggregateRoot;

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
        $aggregate = new BonusAccountAggregateRoot();

        $aggregate->createAccount(
            clientId: $this->dto->clientId,
            initialBalance: 0
        );


        $aggregate->persist();

        return new BonusAccount(
            id: 0,
            clientId: $this->dto->clientId,
            balance: 0,
            createdAt: now(),
            updatedAt: now()
        );
    }
}
