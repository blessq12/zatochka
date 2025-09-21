<?php

namespace App\Filament\Resources\Master\StockItemResource\Pages;

use App\Filament\Resources\Master\StockItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStockItem extends ViewRecord
{
    protected static string $resource = StockItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('use_in_repair')
                ->label('Использовать в ремонте')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\TextInput::make('quantity')
                        ->label('Количество для списания')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->maxValue(fn() => $this->record->quantity),
                    \Filament\Forms\Components\Textarea::make('description')
                        ->label('Описание использования')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    // Здесь будет логика списания запчастей
                    \Filament\Notifications\Notification::make()
                        ->title('Запчасть списана')
                        ->success()
                        ->send();
                })
                ->visible(fn(): bool => $this->record->quantity > 0),
        ];
    }
}
