<?php

namespace App\Application\CRM\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\CRM\Entity\ClientEquipment;
use App\Domain\CRM\Entity\EquipmentComponent;
use App\Domain\CRM\Repository\ClientEquipmentRepository;
use App\Domain\CRM\VO\EquipmentNumber;
use App\Domain\CRM\VO\EquipmentType;
use App\Domain\CRM\VO\SerialNumber;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class RegisterEquipmentHandler
{
    public function __construct(
        private ClientEquipmentRepository $equipment,
        private EntityIdGenerator $ids,
        private DomainEventPublisher $events,
    ) {}

    public function handle(RegisterEquipmentCommand $command): void
    {
        $type = EquipmentType::tryFrom($command->equipmentType)
            ?? throw new DomainException('Unknown equipment type.');

        $aggregate = ClientEquipment::register(
            new EntityId($command->equipmentId),
            EquipmentNumber::fromSequence($this->ids->next('equipment_number')->value),
            $command->title,
            $command->brand,
            $command->modelName,
            $type,
            $command->clientId !== null ? new EntityId($command->clientId) : null,
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
