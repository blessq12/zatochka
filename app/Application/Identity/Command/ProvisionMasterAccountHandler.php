<?php

namespace App\Application\Identity\Command;

use App\Domain\Identity\Entity\MasterAccount;
use App\Domain\Identity\Repository\MasterAccountRepository;
use App\Shared\ValueObject\EntityId;

final readonly class ProvisionMasterAccountHandler
{
    public function __construct(
        private MasterAccountRepository $accounts,
    ) {}

    public function handle(ProvisionMasterAccountCommand $command): void
    {
        $masterId = new EntityId($command->masterId);

        if ($this->accounts->findById($masterId) !== null) {
            return;
        }

        $account = MasterAccount::provision(
            $masterId,
            $command->email,
            $command->passwordHash,
        );

        $this->accounts->save($account);
    }
}
