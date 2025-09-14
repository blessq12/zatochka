<?php

namespace App\Filament\Resources\Manager\StockCategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StockItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockItems';

    protected static ?string $title = 'Товары в категории';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Название товара')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('sku')
                    ->label('SKU/Артикул')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->helperText('Уникальный код товара'),

                Forms\Components\Textarea::make('description')
                    ->label('Описание')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('quantity')
                    ->label('Количество на складе')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),

                Forms\Components\TextInput::make('min_stock')
                    ->label('Минимальный остаток')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),

                Forms\Components\Select::make('unit')
                    ->label('Единица измерения')
                    ->options([
                        'шт' => 'Штуки',
                        'кг' => 'Килограммы',
                        'г' => 'Граммы',
                        'л' => 'Литры',
                        'мл' => 'Миллилитры',
                        'м' => 'Метры',
                        'см' => 'Сантиметры',
                        'м²' => 'Квадратные метры',
                        'м³' => 'Кубические метры',
                        'компл' => 'Комплект',
                        'упак' => 'Упаковка',
                    ])
                    ->default('шт')
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Склад')
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Остаток')
                    ->numeric()
                    ->sortable()
                    ->color(fn($record) => $record->isLowStock() ? 'danger' : 'success')
                    ->formatStateUsing(fn($state, $record) => $state . ' ' . $record->unit),

                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Закупочная')
                    ->money('RUB')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('retail_price')
                    ->label('Розничная')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активные')
                    ->placeholder('Все товары')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Низкий остаток')
                    ->query(fn($query) => $query->lowStock()),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
