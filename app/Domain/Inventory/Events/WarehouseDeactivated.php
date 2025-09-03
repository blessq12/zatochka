<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Inventory\ValueObjects\WarehouseId;

class WarehouseDeactivated extends DomainEvent
{
    public function __construct(
        public readonly WarehouseId $warehouseId
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'WarehouseDeactivated';
    }

    public function eventData(): array
    {
        return [
            'warehouse_id' => (string) $this->warehouseId,
        ];
    }
}
