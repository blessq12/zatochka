<?php

namespace App\Domain\Warehouse\AggregateRoot;

use App\Domain\Warehouse\Event\WarehouseCreated;
use App\Domain\Warehouse\Event\WarehouseUpdated;
use App\Domain\Warehouse\Event\WarehouseActivated;
use App\Domain\Warehouse\Event\WarehouseDeactivated;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Illuminate\Support\Str;

class WarehouseAggregateRoot extends AggregateRoot
{
    public function createWarehouse(
        int $warehouseId,
        int $branchId,
        string $name,
        ?string $description,
        int $createdBy
    ): self {
        $this->recordThat(new WarehouseCreated(
            warehouseId: $warehouseId,
            branchId: $branchId,
            name: $name,
            description: $description,
            createdBy: $createdBy
        ));

        return $this;
    }

    public function updateWarehouse(
        int $warehouseId,
        string $name,
        ?string $description,
        int $updatedBy
    ): self {
        $this->recordThat(new WarehouseUpdated(
            warehouseId: $warehouseId,
            name: $name,
            description: $description,
            updatedBy: $updatedBy
        ));

        return $this;
    }

    public function activateWarehouse(int $warehouseId, int $activatedBy): self
    {
        $this->recordThat(new WarehouseActivated(
            warehouseId: $warehouseId,
            activatedBy: $activatedBy
        ));

        return $this;
    }

    public function deactivateWarehouse(int $warehouseId, int $deactivatedBy): self
    {
        $this->recordThat(new WarehouseDeactivated(
            warehouseId: $warehouseId,
            deactivatedBy: $deactivatedBy
        ));

        return $this;
    }

    public static function create(): self
    {
        return static::retrieve(Str::uuid()->toString());
    }
}
