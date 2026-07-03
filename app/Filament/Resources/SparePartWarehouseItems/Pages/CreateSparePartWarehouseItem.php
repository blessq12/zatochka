<?php

namespace App\Filament\Resources\SparePartWarehouseItems\Pages;

use App\Filament\Resources\SparePartWarehouseItems\SparePartWarehouseItemResource;
use App\Filament\Resources\WarehouseItems\Pages\CreateWarehouseItemRecord;

class CreateSparePartWarehouseItem extends CreateWarehouseItemRecord
{
    protected static string $resource = SparePartWarehouseItemResource::class;
}
