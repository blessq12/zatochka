<?php

namespace App\Application\Identity\Command;

use App\Application\Shared\Port\PasswordHasher;
use App\Domain\Identity\Entity\Master;
use App\Domain\Identity\Repository\ManagerRepository;
use App\Domain\Identity\Repository\MasterRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class RegisterMasterHandler
{
    public function __construct(
        private MasterRepository $masters,
        private ManagerRepository $managers,
        private PasswordHasher $passwords,
    ) {}

    public function handle(RegisterMasterCommand $command): void
    {
        $email = strtolower(trim($command->email));

        if ($this->masters->emailExists($email) || $this->managers->emailExists($email)) {
            throw new DomainException('Staff email is already taken.');
        }

        $master = Master::register(
            new EntityId($command->masterId),
            $command->name,
            $email,
            $this->passwords->hash($command->plainPassword),
        );

        $this->masters->save($master);
    }
}
