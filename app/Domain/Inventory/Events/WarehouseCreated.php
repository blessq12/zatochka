<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Inventory\ValueObjects\WarehouseId;
use App\Domain\Inventory\ValueObjects\WarehouseName;
use App\Domain\Inventory\ValueObjects\BranchId;

class WarehouseCreated extends DomainEvent
{
    public function __construct(
        public readonly WarehouseId $warehouseId,
        public readonly ?BranchId $branchId,
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
            'warehouse_id' => (string) $this->warehouseId,
            'branch_id' => $this->branchId ? (string) $this->branchId : null,
            'name' => (string) $this->name,
        ];
    }
}
