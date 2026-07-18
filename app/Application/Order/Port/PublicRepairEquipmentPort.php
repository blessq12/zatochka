<?php

namespace App\Application\Order\Port;

/**
 * Cross-BC seam: Order Application ensures Equipment aggregate for public repair intake.
 */
interface PublicRepairEquipmentPort
{
    public function ensureForClient(
        int $clientId,
        string $deviceName,
        string $equipmentType,
        ?string $problemDescription = null,
    ): int;
}
