<?php

namespace App\Application\Identity\Command;

use App\Domain\Identity\Entity\ManagerAccount;
use App\Domain\Identity\Repository\ManagerAccountRepository;
use App\Shared\ValueObject\EntityId;

final readonly class ProvisionManagerAccountHandler
{
    public function __construct(
        private ManagerAccountRepository $accounts,
    ) {}

    public function handle(ProvisionManagerAccountCommand $command): void
    {
        $managerId = new EntityId($command->managerId);

        if ($this->accounts->findById($managerId) !== null) {
            return;
        }

        $account = ManagerAccount::provision(
            $managerId,
            $command->email,
            $command->passwordHash,
        );

        $this->accounts->save($account);
    }
}
