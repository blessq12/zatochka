<?php

namespace App\Application\CRM\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\UnitOfWork;
use App\Domain\CRM\Repository\ManagerRepository;
use App\Domain\CRM\Repository\MasterRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class UpdateMasterHandler
{
    public function __construct(
        private MasterRepository $masters,
        private ManagerRepository $managers,
        private DomainEventPublisher $events,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(UpdateMasterCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
            $id = new EntityId($command->masterId);
            $master = $this->masters->getById($id);
            $email = strtolower(trim($command->email));

            if ($this->masters->emailExists($email, $id) || $this->managers->emailExists($email)) {
                throw new DomainException('Staff email is already taken.');
            }

            $master->updateProfile($command->name, $email);
            $this->masters->save($master);
            $this->events->publish($master->pullDomainEvents());
        });
    }
}
