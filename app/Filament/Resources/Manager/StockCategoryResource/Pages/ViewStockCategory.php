<?php

namespace App\Filament\Resources\Manager\StockCategoryResource\Pages;

use App\Filament\Resources\Manager\StockCategoryResource;
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
