<?php

namespace App\Application\Order\Port;

interface EquipmentProvisioningPort
{
    public function register(
        int $equipmentId,
        int $clientId,
        string $title,
        string $brand,
        string $modelName,
        ?string $notes = null,
    ): void;
}
