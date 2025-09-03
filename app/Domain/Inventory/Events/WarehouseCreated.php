<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Inventory\ValueObjects\WarehouseName;

class WarehouseCreated extends DomainEvent
{
    public function __construct(
        public readonly int $warehouseId,
        public readonly ?int $branchId,
        public readonly WarehouseName $name
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'WarehouseCreated';
    }

    public function eventData(): array
    {
        return [
            'warehouse_id' => $this->warehouseId,
            'branch_id' => $this->branchId,
            'name' => (string) $this->name,
        ];
    }
}
