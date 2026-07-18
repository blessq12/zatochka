<?php

namespace App\Application\Equipment\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Equipment\Repository\ClientEquipmentRepository;
use App\Domain\Equipment\VO\EquipmentType;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class UpdateEquipmentHandler
{
    public function __construct(
        private ClientEquipmentRepository $equipment,
        private DomainEventPublisher $events,
    ) {}

    public function handle(UpdateEquipmentCommand $command): void
    {
        $type = EquipmentType::tryFrom($command->equipmentType)
            ?? throw new DomainException('Unknown equipment type.');

        $aggregate = $this->equipment->getById(new EntityId($command->equipmentId));
        $aggregate->updateProfile(
            $command->title,
            $command->brand,
            $command->modelName,
            $type,
            $command->notes,
            $command->clientId !== null ? new EntityId($command->clientId) : null,
        );
        $this->equipment->save($aggregate);
        $this->events->publish($aggregate->pullDomainEvents());
    }
}
