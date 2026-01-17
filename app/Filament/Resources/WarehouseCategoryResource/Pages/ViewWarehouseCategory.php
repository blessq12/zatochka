<?php

namespace App\Filament\Resources\WarehouseCategoryResource\Pages;

use App\Filament\Resources\WarehouseCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWarehouseCategory extends ViewRecord
{
    protected static string $resource = WarehouseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
