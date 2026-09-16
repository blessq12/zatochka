<?php

namespace App\Application\Identity\Command;

use App\Application\Shared\Port\PasswordHasher;
use App\Domain\Identity\Repository\ManagerRepository;
use App\Shared\ValueObject\EntityId;

final readonly class ChangeManagerPasswordHandler
{
    public function __construct(
        private ManagerRepository $managers,
        private PasswordHasher $passwords,
    ) {}

    public function handle(ChangeManagerPasswordCommand $command): void
    {
        $manager = $this->managers->getById(new EntityId($command->managerId));
        $manager->changePassword($this->passwords->hash($command->plainPassword));
        $this->managers->save($manager);
    }
}
