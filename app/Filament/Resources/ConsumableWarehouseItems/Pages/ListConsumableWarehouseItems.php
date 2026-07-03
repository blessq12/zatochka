<?php

namespace App\Filament\Resources\ConsumableWarehouseItems\Pages;

use App\Filament\Resources\ConsumableWarehouseItems\ConsumableWarehouseItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConsumableWarehouseItems extends ListRecords
{
    protected static string $resource = ConsumableWarehouseItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
