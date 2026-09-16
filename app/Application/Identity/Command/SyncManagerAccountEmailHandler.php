<?php

namespace App\Application\Identity\Command;

use App\Domain\Identity\Repository\ManagerAccountRepository;
use App\Shared\ValueObject\EntityId;

final readonly class SyncManagerAccountEmailHandler
{
    public function __construct(
        private ManagerAccountRepository $accounts,
    ) {}

    public function handle(SyncManagerAccountEmailCommand $command): void
    {
        $account = $this->accounts->getById(new EntityId($command->managerId));
        $account->syncEmail($command->email);
        $this->accounts->save($account);
    }
}
