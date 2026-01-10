<?php

namespace App\Filament\Resources\StockCategoryResource\Pages;

use App\Filament\Resources\StockCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStockCategory extends ViewRecord
{
    protected static string $resource = StockCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
