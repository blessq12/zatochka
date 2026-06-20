<?php

namespace App\Filament\Resources\SparePartWarehouseItems\Pages;

use App\Filament\Resources\SparePartWarehouseItems\SparePartWarehouseItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSparePartWarehouseItems extends ListRecords
{
    protected static string $resource = SparePartWarehouseItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
