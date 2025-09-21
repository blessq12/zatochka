<?php

namespace App\Domain\Repair\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class RepairCreated extends ShouldBeStored
{
    public function __construct(
        public int $repairId,
        public string $number,
        public int $orderId,
        public ?int $masterId,
        public string $status,
        public ?string $description,
        public ?string $diagnosis,
        public array $partsUsed = [],
        public array $additionalData = []
    ) {}
}
