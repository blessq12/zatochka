<?php

namespace App\Application\Identity\Command;

use App\Application\Shared\Port\PasswordHasher;
use App\Domain\Identity\Repository\ManagerRepository;
use App\Domain\Identity\Repository\MasterRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class UpdateManagerHandler
{
    public function __construct(
        private ManagerRepository $managers,
        private MasterRepository $masters,
        private PasswordHasher $passwords,
    ) {}

    public function handle(UpdateManagerCommand $command): void
    {
        $id = new EntityId($command->managerId);
        $manager = $this->managers->getById($id);
        $email = strtolower(trim($command->email));

        if ($this->managers->emailExists($email, $id) || $this->masters->emailExists($email)) {
            throw new DomainException('Staff email is already taken.');
        }

        $manager->updateProfile($command->name, $email);

        if ($command->plainPassword !== null && $command->plainPassword !== '') {
            $manager->changePassword($this->passwords->hash($command->plainPassword));
        }

        $this->managers->save($manager);
    }
}
