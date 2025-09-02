<?php

namespace App\Filament\Resources\Master\InventoryTransactionResource\Pages;

use App\Filament\Resources\Master\InventoryTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventoryTransactions extends ListRecords
{
    protected static string $resource = InventoryTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Добавить операцию'),
        ];
    }
}
