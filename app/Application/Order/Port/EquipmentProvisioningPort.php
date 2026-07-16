<?php

namespace App\Application\Order\Port;

use App\Application\Equipment\DTO\EquipmentPartDTO;

interface EquipmentProvisioningPort
{
    /**
     * @param list<EquipmentPartDTO> $parts
     */
    public function register(
        int $equipmentId,
        int $clientId,
        string $title,
        string $brand,
        string $modelName,
        ?string $notes = null,
        array $parts = [],
    ): void;
}
