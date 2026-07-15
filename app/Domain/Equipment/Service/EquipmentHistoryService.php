<?php

namespace App\Domain\Equipment\Service;

use App\Domain\Equipment\Entity\ClientEquipment;
use App\Domain\Equipment\Entity\RepairHistoryEntry;
use App\Shared\ValueObject\EntityId;

final class EquipmentHistoryService
{
    public function recordRepair(
        ClientEquipment $equipment,
        EntityId $historyEntryId,
        EntityId $orderItemId,
        string $summary,
    ): void {
        $equipment->appendRepairHistory(new RepairHistoryEntry(
            $historyEntryId,
            $orderItemId,
            $summary,
        ));
    }
}
