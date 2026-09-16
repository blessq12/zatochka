<?php

namespace App\Application\CRM\Query;

use App\Application\CRM\DTO\ClientEquipmentDTO;
use App\Application\CRM\ReadPort\EquipmentReadPort;

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
