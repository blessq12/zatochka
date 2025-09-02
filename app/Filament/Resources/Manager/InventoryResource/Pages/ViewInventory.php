<?php

namespace App\Filament\Resources\Manager\InventoryResource\Pages;

use App\Filament\Resources\Manager\InventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInventory extends ViewRecord
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Редактировать'),
        ];
    }
}
