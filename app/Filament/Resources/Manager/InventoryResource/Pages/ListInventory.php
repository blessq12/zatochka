<?php

namespace App\Filament\Resources\Manager\InventoryResource\Pages;

use App\Filament\Resources\Manager\InventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventory extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Добавить товар'),
        ];
    }
}
