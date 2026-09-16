<?php

namespace App\Application\Identity\Command;

use App\Application\Shared\Port\PasswordHasher;
use App\Domain\Identity\Repository\ManagerRepository;
use App\Domain\Identity\Repository\MasterRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class UpdateMasterHandler
{
    public function __construct(
        private MasterRepository $masters,
        private ManagerRepository $managers,
        private PasswordHasher $passwords,
    ) {}

    public function handle(UpdateMasterCommand $command): void
    {
        $id = new EntityId($command->masterId);
        $master = $this->masters->getById($id);
        $email = strtolower(trim($command->email));

        if ($this->masters->emailExists($email, $id) || $this->managers->emailExists($email)) {
            throw new DomainException('Staff email is already taken.');
        }

        $master->updateProfile($command->name, $email);

        if ($command->plainPassword !== null && $command->plainPassword !== '') {
            $master->changePassword($this->passwords->hash($command->plainPassword));
        }

        $this->masters->save($master);
    }
}
