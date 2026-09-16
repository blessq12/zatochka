<?php

namespace App\Application\Identity\Command;

use App\Application\Shared\Port\PasswordHasher;
use App\Domain\Identity\Repository\MasterAccountRepository;
use App\Shared\ValueObject\EntityId;

final readonly class ChangeMasterPasswordHandler
{
    public function __construct(
        private MasterAccountRepository $accounts,
        private PasswordHasher $passwords,
    ) {}

    public function handle(ChangeMasterPasswordCommand $command): void
    {
        $account = $this->accounts->getById(new EntityId($command->masterId));
        $account->changePassword($this->passwords->hash($command->plainPassword));
        $this->accounts->save($account);
    }
}
