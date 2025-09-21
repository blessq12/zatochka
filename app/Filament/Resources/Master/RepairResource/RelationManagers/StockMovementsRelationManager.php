<?php

namespace App\Filament\Resources\Master\RepairResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StockMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('warehouse_id')
                    ->label('Склад')
                    ->relationship('warehouse', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state, Forms\Get $get) {
                        // Обновляем доступные запчасти при смене склада
                        $set('stock_item_id', null);
                        $set('unit_price', null);
                        $set('total_amount', null);
                    }),

                Forms\Components\Select::make('stock_item_id')
                    ->label('Запчасть')
                    ->relationship('stockItem', 'name', function ($query, Forms\Get $get) {
                        $warehouseId = $get('warehouse_id');
                        if ($warehouseId) {
                            return $query->where('warehouse_id', $warehouseId)
                                ->where('is_active', true)
                                ->where('is_deleted', false)
                                ->where('quantity', '>', 0);
                        }
                        return $query->where('id', 0); // Пустой результат если склад не выбран
                    })
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name . ' (' . $record->sku . ') - ' . number_format($record->retail_price, 2) . '₽')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state, Forms\Get $get) {
                        if ($state) {
                            // Получаем розничную цену товара
                            $stockItem = \App\Models\StockItem::find($state);
                            if ($stockItem) {
                                $set('unit_price', $stockItem->retail_price);
                                $quantity = $get('quantity') ?? 0;
                                $set('total_amount', $quantity * $stockItem->retail_price);
                            }
                        }
                    }),

                Forms\Components\TextInput::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state, Forms\Get $get) {
                        $quantity = $state ?? 0;
                        $unitPrice = $get('unit_price') ?? 0;
                        $set('total_amount', $quantity * $unitPrice);
                    }),

                Forms\Components\TextInput::make('unit_price')
                    ->label('Цена за единицу')
                    ->numeric()
                    ->prefix('₽')
                    ->step(0.01)
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Цена берется из розничной цены товара'),

                Forms\Components\TextInput::make('total_amount')
                    ->label('Общая сумма')
                    ->numeric()
                    ->prefix('₽')
                    ->step(0.01)
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\Textarea::make('description')
                    ->label('Описание')
                    ->rows(2),

                Forms\Components\Hidden::make('movement_type')
                    ->default('out'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('stockItem.name')
            ->columns([
                Tables\Columns\TextColumn::make('stockItem.name')
                    ->label('Запчасть')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stockItem.sku')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Количество')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Цена за единицу')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Общая сумма')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50)
                    ->tooltip(fn($state): ?string => strlen($state) > 50 ? $state : null),

                Tables\Columns\TextColumn::make('movement_date')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stock_item_id')
                    ->label('Запчасть')
                    ->relationship('stockItem', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                //
            ])
            ->defaultSort('movement_date', 'desc');
    }
}
