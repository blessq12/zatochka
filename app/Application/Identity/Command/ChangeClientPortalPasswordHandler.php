<?php

namespace App\Application\Identity\Command;

use App\Application\Shared\Port\PasswordHasher;
use App\Domain\Identity\Repository\ClientAccountRepository;
use App\Shared\ValueObject\EntityId;

final readonly class ChangeClientPortalPasswordHandler
{
    public function __construct(
        private ClientAccountRepository $accounts,
        private PasswordHasher $passwords,
    ) {}

    public function handle(ChangeClientPortalPasswordCommand $command): void
    {
        $account = $this->accounts->getByClientId(new EntityId($command->clientId));
        $account->changePassword($this->passwords->hash($command->plainPassword));
        $this->accounts->save($account);
    }
}
