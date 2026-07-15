<?php

namespace App\Application\Equipment\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Equipment\Entity\EquipmentComponent;
use App\Domain\Equipment\Repository\ClientEquipmentRepository;
use App\Shared\ValueObject\EntityId;

final readonly class AddComponentHandler
{
    public function __construct(
        private ClientEquipmentRepository $equipment,
        private DomainEventPublisher $events,
    ) {}

    public function handle(AddComponentCommand $command): void
    {
        $aggregate = $this->equipment->getById(new EntityId($command->equipmentId));
        $aggregate->addComponent(new EquipmentComponent(
            new EntityId($command->componentId),
            $command->name,
        ));
        $this->equipment->save($aggregate);
        $this->events->publish($aggregate->pullDomainEvents());
    }
}
