<?php

namespace App\Application\CRM\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\Port\PasswordHasher;
use App\Application\Shared\UnitOfWork;
use App\Domain\CRM\Entity\Master;
use App\Domain\CRM\Repository\ManagerRepository;
use App\Domain\CRM\Repository\MasterRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class RegisterMasterHandler
{
    public function __construct(
        private MasterRepository $masters,
        private ManagerRepository $managers,
        private PasswordHasher $passwords,
        private DomainEventPublisher $events,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(RegisterMasterCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
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
            $this->events->publish($master->pullDomainEvents());
        });
    }
}
