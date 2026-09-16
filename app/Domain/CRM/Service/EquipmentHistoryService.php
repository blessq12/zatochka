<?php

namespace App\Domain\CRM\Service;

use App\Domain\CRM\Entity\ClientEquipment;
use App\Domain\CRM\Entity\RepairHistoryEntry;
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
