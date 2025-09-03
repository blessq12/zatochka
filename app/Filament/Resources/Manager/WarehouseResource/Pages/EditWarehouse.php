<?php

namespace App\Filament\Resources\Manager\WarehouseResource\Pages;

use App\Filament\Resources\Manager\WarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWarehouse extends EditRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить склад'),
        ];
    }
}
