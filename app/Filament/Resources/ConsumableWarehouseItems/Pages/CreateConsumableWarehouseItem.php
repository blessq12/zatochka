<?php

namespace App\Filament\Resources\ConsumableWarehouseItems\Pages;

use App\Filament\Resources\ConsumableWarehouseItems\ConsumableWarehouseItemResource;
use App\Filament\Resources\WarehouseItems\Pages\CreateWarehouseItemRecord;

class CreateConsumableWarehouseItem extends CreateWarehouseItemRecord
{
    protected static string $resource = ConsumableWarehouseItemResource::class;
}
