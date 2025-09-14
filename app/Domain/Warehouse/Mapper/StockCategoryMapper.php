<?php

namespace App\Domain\Warehouse\Mapper;

use App\Domain\Warehouse\Entity\StockCategory;
use Illuminate\Database\Eloquent\Model;

interface StockCategoryMapper
{
    public function toDomain($eloquentModel): StockCategory;

    public function toEloquent(StockCategory $domainEntity): array;

    public function toEloquentModel(StockCategory $domainEntity): Model;
}
