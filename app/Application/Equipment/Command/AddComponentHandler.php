<?php

namespace App\Application\Equipment\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Equipment\Entity\EquipmentComponent;
use App\Domain\Equipment\Repository\ClientEquipmentRepository;
use App\Domain\Equipment\VO\SerialNumber;
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

        $serial = $command->serialNumber !== null && trim($command->serialNumber) !== ''
            ? new SerialNumber($command->serialNumber)
            : null;

        $aggregate->addComponent(
            new EquipmentComponent(new EntityId($command->componentId), $command->name),
            $serial,
        );

        $this->equipment->save($aggregate);
        $this->events->publish($aggregate->pullDomainEvents());
    }
}
