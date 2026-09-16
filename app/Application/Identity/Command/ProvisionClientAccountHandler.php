<?php

namespace App\Application\Identity\Command;

use App\Domain\Identity\Entity\ClientAccount;
use App\Domain\Identity\Repository\ClientAccountRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Phone;

final readonly class ProvisionClientAccountHandler
{
    public function __construct(
        private ClientAccountRepository $accounts,
    ) {}

    public function handle(ProvisionClientAccountCommand $command): void
    {
        $clientId = new EntityId($command->clientId);

        if ($this->accounts->findByClientId($clientId) !== null) {
            return;
        }

        $phone = new Phone($command->phone);

        if ($this->accounts->findByPhone($phone) !== null) {
            throw new DomainException('Client account phone is already taken.');
        }

        $account = ClientAccount::provision(
            new EntityId($command->accountId),
            $clientId,
            $phone,
            $command->passwordHash,
        );

        $this->accounts->save($account);
    }
}
