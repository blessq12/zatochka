<?php

namespace App\Application\Equipment\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Equipment\Entity\ClientEquipment;
use App\Domain\Equipment\Entity\EquipmentComponent;
use App\Domain\Equipment\Repository\ClientEquipmentRepository;
use App\Domain\Equipment\VO\SerialNumber;
use App\Shared\ValueObject\EntityId;

final readonly class RegisterEquipmentHandler
{
    public function __construct(
        private ClientEquipmentRepository $equipment,
        private DomainEventPublisher $events,
    ) {}

    public function handle(RegisterEquipmentCommand $command): void
    {
        $aggregate = ClientEquipment::register(
            new EntityId($command->equipmentId),
            $command->title,
            $command->brand,
            $command->modelName,
            $command->clientId !== null ? new EntityId($command->clientId) : null,
            $command->notes,
        );

        foreach ($command->parts as $part) {
            $serial = $part->serialNumber !== null && trim($part->serialNumber) !== ''
                ? new SerialNumber($part->serialNumber)
                : null;

            $aggregate->addComponent(
                new EquipmentComponent(new EntityId($part->componentId), $part->name),
                $serial,
            );
        }

        $this->equipment->save($aggregate);
        $this->events->publish($aggregate->pullDomainEvents());
    }
}
