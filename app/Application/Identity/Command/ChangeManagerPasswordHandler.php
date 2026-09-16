<?php

namespace App\Application\Identity\Command;

use App\Application\Shared\Port\PasswordHasher;
use App\Domain\Identity\Repository\ManagerAccountRepository;
use App\Shared\ValueObject\EntityId;

final readonly class ChangeManagerPasswordHandler
{
    public function __construct(
        private ManagerAccountRepository $accounts,
        private PasswordHasher $passwords,
    ) {}

    public function handle(ChangeManagerPasswordCommand $command): void
    {
        $account = $this->accounts->getById(new EntityId($command->managerId));
        $account->changePassword($this->passwords->hash($command->plainPassword));
        $this->accounts->save($account);
    }
}
