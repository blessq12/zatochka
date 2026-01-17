<?php

namespace App\Filament\Resources\WarehouseCategoryResource\Pages;

use App\Filament\Resources\WarehouseCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarehouseCategories extends ListRecords
{
    protected static string $resource = WarehouseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
