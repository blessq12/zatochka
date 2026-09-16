<?php

namespace App\Application\CRM\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\CRM\Entity\EquipmentComponent;
use App\Domain\CRM\Repository\ClientEquipmentRepository;
use App\Domain\CRM\VO\SerialNumber;
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
