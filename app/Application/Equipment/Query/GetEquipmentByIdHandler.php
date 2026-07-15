<?php

namespace App\Application\Equipment\Query;

use App\Application\Equipment\DTO\ClientEquipmentDTO;
use App\Application\Equipment\ReadPort\EquipmentReadPort;

final readonly class GetEquipmentByIdHandler
{
    public function __construct(
        private EquipmentReadPort $readPort,
    ) {}

    public function handle(GetEquipmentByIdQuery $query): ?ClientEquipmentDTO
    {
        return $this->readPort->findById($query->equipmentId);
    }
}
