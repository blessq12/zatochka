<?php

namespace App\Domain\Repair\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class RepairStatusChanged extends ShouldBeStored
{
    public function __construct(
        public int $repairId,
        public string $oldStatus,
        public string $newStatus,
        public ?string $reason = null,
        public ?\DateTime $changedAt = null
    ) {
        $this->changedAt = $changedAt ?? new \DateTime();
    }
}
