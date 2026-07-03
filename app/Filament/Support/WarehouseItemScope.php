<?php

namespace App\Filament\Support;

use App\Domain\Warehouse\Enum\WarehouseItemType;
use App\Infrastructure\Warehouse\Persistence\Eloquent\WarehouseItemModel;
use Illuminate\Database\Eloquent\Builder;

final class WarehouseItemScope
{
    /** @param Builder<WarehouseItemModel> $query */
    public static function byType(Builder $query, WarehouseItemType $type): Builder
    {
        return $query->where('type', $type);
    }
}
