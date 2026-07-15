<?php

namespace App\Application\Equipment\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Equipment\Repository\ClientEquipmentRepository;
use App\Shared\ValueObject\EntityId;

final readonly class UpdateEquipmentHandler
{
    public function __construct(
        private ClientEquipmentRepository $equipment,
        private DomainEventPublisher $events,
    ) {}

    public function handle(UpdateEquipmentCommand $command): void
    {
        $aggregate = $this->equipment->getById(new EntityId($command->equipmentId));
        $aggregate->updateProfile(
            $command->title,
            $command->brand,
            $command->modelName,
            $command->notes,
            $command->clientId !== null ? new EntityId($command->clientId) : null,
        );
        $this->equipment->save($aggregate);
        $this->events->publish($aggregate->pullDomainEvents());
    }
}
