<?php

namespace App\Filament\Resources\WarehouseCategoryResource\Pages;

use App\Filament\Resources\WarehouseCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWarehouseCategory extends EditRecord
{
    protected static string $resource = WarehouseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
