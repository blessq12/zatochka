<?php

namespace App\Application\CRM\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\UnitOfWork;
use App\Domain\CRM\Repository\ManagerRepository;
use App\Domain\CRM\Repository\MasterRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class UpdateManagerHandler
{
    public function __construct(
        private ManagerRepository $managers,
        private MasterRepository $masters,
        private DomainEventPublisher $events,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(UpdateManagerCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
            $id = new EntityId($command->managerId);
            $manager = $this->managers->getById($id);
            $email = strtolower(trim($command->email));

            if ($this->managers->emailExists($email, $id) || $this->masters->emailExists($email)) {
                throw new DomainException('Staff email is already taken.');
            }

            $manager->updateProfile($command->name, $email);
            $this->managers->save($manager);
            $this->events->publish($manager->pullDomainEvents());
        });
    }
}
