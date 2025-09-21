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
                Forms\Components\Select::make('stock_item_id')
                    ->label('Запчасть')
                    ->relationship('stockItem', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name . ' (' . $record->sku . ')')
                    ->searchable()
                    ->preload()
                    ->required(),

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
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state, Forms\Get $get) {
                        $quantity = $get('quantity') ?? 0;
                        $unitPrice = $state ?? 0;
                        $set('total_amount', $quantity * $unitPrice);
                    }),

                Forms\Components\TextInput::make('total_amount')
                    ->label('Общая сумма')
                    ->numeric()
                    ->prefix('₽')
                    ->step(0.01)
                    ->required()
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\Textarea::make('description')
                    ->label('Описание')
                    ->rows(2),

                Forms\Components\Hidden::make('movement_type')
                    ->default('out'),

                Forms\Components\Hidden::make('warehouse_id'),
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
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        $data['total_amount'] = ($data['quantity'] ?? 0) * ($data['unit_price'] ?? 0);
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('movement_date', 'desc');
    }
}
