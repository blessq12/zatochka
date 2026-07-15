<?php

namespace App\Application\Equipment\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Equipment\Repository\ClientEquipmentRepository;
use App\Domain\Equipment\VO\SerialNumber;
use App\Shared\ValueObject\EntityId;

final readonly class RegisterSerialNumberHandler
{
    public function __construct(
        private ClientEquipmentRepository $equipment,
        private DomainEventPublisher $events,
    ) {}

    public function handle(RegisterSerialNumberCommand $command): void
    {
        $aggregate = $this->equipment->getById(new EntityId($command->equipmentId));
        $aggregate->registerComponentSerial(
            new EntityId($command->componentId),
            new SerialNumber($command->serialNumber),
        );
        $this->equipment->save($aggregate);
        $this->events->publish($aggregate->pullDomainEvents());
    }
}
