<?php

namespace App\Filament\Resources\Manager\StockCategoryResource\Pages;

use App\Filament\Resources\Manager\StockCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockCategory extends EditRecord
{
    protected static string $resource = StockCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
