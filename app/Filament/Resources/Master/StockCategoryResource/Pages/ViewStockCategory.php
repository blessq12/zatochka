<?php

namespace App\Filament\Resources\Master\StockCategoryResource\Pages;

use App\Filament\Resources\Master\StockCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStockCategory extends ViewRecord
{
    protected static string $resource = StockCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_stock_items')
                ->label('Запчасти в категории')
                ->icon('heroicon-o-cube')
                ->url(fn(): string => route('filament.master.resources.master.stock-items.index', ['tableFilters[category_id][value]' => $this->record->id])),
        ];
    }
}
