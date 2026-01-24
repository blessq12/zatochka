<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\WarehouseItemResource;
use App\Models\WarehouseItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockWidget extends BaseWidget
{
    protected static ?string $heading = 'Товары с низким остатком';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                WarehouseItem::query()
                    ->where('is_active', true)
                    ->lowStock()
                    ->with('category')
                    ->orderByRaw('(quantity - min_quantity) ASC')
            )
            ->columns([
                Tables\Columns\TextColumn::make('article')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категория')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Количество')
                    ->numeric(
                        decimalPlaces: 3,
                        decimalSeparator: ',',
                        thousandsSeparator: ' ',
                    )
                    ->sortable()
                    ->color(fn (WarehouseItem $record): string => 
                        $record->quantity <= 0 ? 'danger' : 'warning'
                    )
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('min_quantity')
                    ->label('Мин. порог')
                    ->numeric(
                        decimalPlaces: 3,
                        decimalSeparator: ',',
                        thousandsSeparator: ' ',
                    )
                    ->sortable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Ед. изм.')
                    ->badge()
                    ->color('secondary'),

                Tables\Columns\TextColumn::make('location')
                    ->label('Местоположение')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Открыть')
                    ->url(fn (WarehouseItem $record): string => WarehouseItemResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ])
            ->emptyStateHeading('Все товары в норме')
            ->emptyStateDescription('Нет товаров с количеством ниже минимального порога')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
