<?php

namespace App\Domain\Warehouse\Mapper;

use App\Domain\Warehouse\Entity\StockItem;
use Illuminate\Database\Eloquent\Model;

interface StockItemMapper
{
    public function toDomain($eloquentModel): StockItem;

    public function toEloquent(StockItem $domainEntity): array;

    public function toEloquentModel(StockItem $domainEntity): Model;
}
