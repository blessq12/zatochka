<?php

namespace App\Application\Crm\Command;

use App\Domain\Crm\Repository\EquipmentRepository;

final readonly class DeleteEquipmentHandler
{
    public function __construct(
        private EquipmentRepository $equipments,
    ) {}

    public function handle(int $id): bool
    {
        $equipment = $this->equipments->findById($id);
        if ($equipment === null) {
            return false;
        }

        $this->equipments->delete($equipment);

        return true;
    }
}
