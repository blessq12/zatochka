<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;

class WarehouseDeactivated extends DomainEvent
{
    public function __construct(
        public readonly int $warehouseId
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
            'warehouse_id' => $this->warehouseId,
        ];
    }
}
