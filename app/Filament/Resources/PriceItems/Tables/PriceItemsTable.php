<?php

namespace App\Filament\Resources\PriceItems\Tables;

use App\Domain\Pricing\Enum\PricePrefix;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PriceItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable(),
                TextColumn::make('price_prefix')
                    ->label('Префикс')
                    ->formatStateUsing(fn (?PricePrefix $state): string => $state?->label() ?? '—'),
                TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
