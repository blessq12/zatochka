<?php

namespace App\Filament\Resources\Master\StockItemResource\Pages;

use App\Filament\Resources\Master\StockItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockItems extends ListRecords
{
    protected static string $resource = StockItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
