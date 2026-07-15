<?php

namespace App\Application\Equipment\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Equipment\Entity\ClientEquipment;
use App\Domain\Equipment\Repository\ClientEquipmentRepository;
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
            new EntityId($command->clientId),
            $command->title,
            $command->notes,
        );

        $this->equipment->save($aggregate);
        $this->events->publish($aggregate->pullDomainEvents());
    }
}
