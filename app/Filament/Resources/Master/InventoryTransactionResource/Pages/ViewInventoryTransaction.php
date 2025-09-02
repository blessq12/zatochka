<?php

namespace App\Filament\Resources\Master\InventoryTransactionResource\Pages;

use App\Filament\Resources\Master\InventoryTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInventoryTransaction extends ViewRecord
{
    protected static string $resource = InventoryTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Редактировать'),
        ];
    }
}
