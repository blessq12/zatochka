<?php

namespace App\Application\Identity\Command;

use App\Domain\Identity\Repository\MasterAccountRepository;
use App\Shared\ValueObject\EntityId;

final readonly class SyncMasterAccountEmailHandler
{
    public function __construct(
        private MasterAccountRepository $accounts,
    ) {}

    public function handle(SyncMasterAccountEmailCommand $command): void
    {
        $account = $this->accounts->getById(new EntityId($command->masterId));
        $account->syncEmail($command->email);
        $this->accounts->save($account);
    }
}
