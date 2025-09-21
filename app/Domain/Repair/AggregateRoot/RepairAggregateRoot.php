<?php

namespace App\Domain\Repair\AggregateRoot;

use App\Domain\Repair\Event\RepairCreated;
use App\Domain\Repair\Event\RepairStatusChanged;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class RepairAggregateRoot extends AggregateRoot
{
    public function createRepair(
        string $number,
        int $orderId,
        ?int $masterId,
        string $status,
        ?string $description = null,
        ?string $diagnosis = null,
        array $partsUsed = [],
        array $additionalData = []
    ): self {
        $this->recordThat(new RepairCreated(
            repairId: $this->uuid(),
            number: $number,
            orderId: $orderId,
            masterId: $masterId,
            status: $status,
            description: $description,
            diagnosis: $diagnosis,
            partsUsed: $partsUsed,
            additionalData: $additionalData
        ));

        return $this;
    }

    public function changeStatus(
        string $newStatus,
        ?string $reason = null
    ): self {
        $this->recordThat(new RepairStatusChanged(
            repairId: $this->uuid(),
            oldStatus: $this->status ?? 'pending',
            newStatus: $newStatus,
            reason: $reason
        ));

        return $this;
    }
}
