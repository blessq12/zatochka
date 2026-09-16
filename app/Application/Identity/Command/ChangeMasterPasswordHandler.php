<?php

namespace App\Application\Identity\Command;

use App\Application\Shared\Port\PasswordHasher;
use App\Domain\Identity\Repository\MasterRepository;
use App\Shared\ValueObject\EntityId;

final readonly class ChangeMasterPasswordHandler
{
    public function __construct(
        private MasterRepository $masters,
        private PasswordHasher $passwords,
    ) {}

    public function handle(ChangeMasterPasswordCommand $command): void
    {
        $master = $this->masters->getById(new EntityId($command->masterId));
        $master->changePassword($this->passwords->hash($command->plainPassword));
        $this->masters->save($master);
    }
}
