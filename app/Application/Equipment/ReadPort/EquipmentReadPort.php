<?php

namespace App\Application\Equipment\ReadPort;

use App\Application\Equipment\DTO\ClientEquipmentDTO;

interface EquipmentReadPort
{
    public function findById(int $equipmentId): ?ClientEquipmentDTO;

    /** @return list<ClientEquipmentDTO> */
    public function listByClientId(int $clientId): array;
}
