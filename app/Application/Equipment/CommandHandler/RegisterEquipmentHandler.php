<?php

namespace App\Application\Equipment\CommandHandler;

use App\Application\Equipment\Command\RegisterEquipmentCommand;
use App\Domain\Equipment\Entity\Equipment;
use App\Domain\Equipment\Event\EquipmentRegistered;
use App\Domain\Equipment\Repository\EquipmentRepositoryInterface;

final class RegisterEquipmentHandler
{
    public function __construct(
        private EquipmentRepositoryInterface $equipment,
    ) {}

    public function handle(RegisterEquipmentCommand $command): Equipment
    {
        $entity = Equipment::register(
            name: $command->name,
            serialNumbers: $command->serialNumbers,
            brand: $command->brand,
            model: $command->model,
        );

        $saved = $this->equipment->save($entity);

        event(new EquipmentRegistered($saved));

        return $saved;
    }
}
