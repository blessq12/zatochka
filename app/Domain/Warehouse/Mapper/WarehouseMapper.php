<?php

namespace App\Domain\Warehouse\Mapper;

use App\Domain\Warehouse\Entity\Warehouse;
use App\Models\Warehouse as EloquentWarehouse;

interface WarehouseMapper
{
    public function toDomain(EloquentWarehouse $eloquentModel): Warehouse;

    public function toEloquent(Warehouse $domainEntity): array;

    public function fromArray(array $data): Warehouse;
}
