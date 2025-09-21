<?php

namespace App\Filament\Resources\Master\WarehouseResource\Pages;

use App\Filament\Resources\Master\WarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWarehouse extends ViewRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
