<?php

namespace App\Application\CRM\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\Port\PasswordHasher;
use App\Application\Shared\UnitOfWork;
use App\Domain\CRM\Entity\Manager;
use App\Domain\CRM\Repository\ManagerRepository;
use App\Domain\CRM\Repository\MasterRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class RegisterManagerHandler
{
    public function __construct(
        private ManagerRepository $managers,
        private MasterRepository $masters,
        private PasswordHasher $passwords,
        private DomainEventPublisher $events,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(RegisterManagerCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
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
            $this->events->publish($manager->pullDomainEvents());
        });
    }
}
