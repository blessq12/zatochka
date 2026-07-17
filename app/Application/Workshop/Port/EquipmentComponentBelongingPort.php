<?php

namespace App\Application\Workshop\Port;

/**
 * Workshop-owned read gate: does a component belong to an order-item equipment?
 */
interface EquipmentComponentBelongingPort
{
    public function belongsToEquipment(int $equipmentId, int $componentId): bool;
}
