<?php

namespace App\Domain\Warehouse\Mapper;

use App\Domain\Warehouse\Entity\StockMovement;
use Illuminate\Database\Eloquent\Model;

interface StockMovementMapper
{
    public function toDomain($eloquentModel): StockMovement;

    public function toEloquent(StockMovement $domainEntity): array;

    public function toEloquentModel(StockMovement $domainEntity): Model;
}
