<?php

namespace App\Application\Identity\Command;

use App\Application\Shared\Port\PasswordHasher;
use App\Domain\Identity\Entity\Manager;
use App\Domain\Identity\Repository\ManagerRepository;
use App\Domain\Identity\Repository\MasterRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class RegisterManagerHandler
{
    public function __construct(
        private ManagerRepository $managers,
        private MasterRepository $masters,
        private PasswordHasher $passwords,
    ) {}

    public function handle(RegisterManagerCommand $command): void
    {
        $email = strtolower(trim($command->email));

        if ($this->managers->emailExists($email) || $this->masters->emailExists($email)) {
            throw new DomainException('Staff email is already taken.');
        }

        $manager = Manager::register(
            new EntityId($command->managerId),
            $command->name,
            $email,
            $this->passwords->hash($command->plainPassword),
        );

        $this->managers->save($manager);
    }
}
