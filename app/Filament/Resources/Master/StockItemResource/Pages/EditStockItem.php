<?php

namespace App\Filament\Resources\Master\StockItemResource\Pages;

use App\Filament\Resources\Master\StockItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockItem extends EditRecord
{
    protected static string $resource = StockItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
