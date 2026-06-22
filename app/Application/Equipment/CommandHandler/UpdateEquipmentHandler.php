<?php

namespace App\Application\Equipment\CommandHandler;

use App\Application\Equipment\Command\UpdateEquipmentCommand;
use App\Domain\Equipment\Entity\Equipment;
use App\Domain\Equipment\Exception\EquipmentNotFoundException;
use App\Domain\Equipment\Repository\EquipmentRepositoryInterface;

final class UpdateEquipmentHandler
{
    public function __construct(
        private EquipmentRepositoryInterface $equipment,
    ) {}

    public function handle(UpdateEquipmentCommand $command): Equipment
    {
        $existing = $this->equipment->findById($command->equipmentId);

        if ($existing === null) {
            throw EquipmentNotFoundException::withId($command->equipmentId);
        }

        $updated = $existing->withDetails(
            name: $command->name,
            serialNumbers: $command->serialNumbers,
            brand: $command->brand,
            model: $command->model,
        );

        return $this->equipment->save($updated);
    }
}
