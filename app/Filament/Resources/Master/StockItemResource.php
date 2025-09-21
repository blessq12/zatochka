<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\StockItemResource\Pages;
use App\Models\StockItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockItemResource extends Resource
{
    protected static ?string $model = StockItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Склад';
    protected static ?string $pluralLabel = 'Запчасти';
    protected static ?string $modelLabel = 'Запчасть';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('sku')
                            ->label('Артикул')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('category_id')
                            ->label('Категория')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('warehouse_id')
                            ->label('Склад')
                            ->relationship('warehouse', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make('Количество и цены')
                    ->schema([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\TextInput::make('min_stock')
                            ->label('Минимальное количество')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\TextInput::make('purchase_price')
                            ->label('Закупочная цена')
                            ->numeric()
                            ->prefix('₽')
                            ->step(0.01),

                        Forms\Components\TextInput::make('retail_price')
                            ->label('Розничная цена')
                            ->numeric()
                            ->prefix('₽')
                            ->step(0.01),

                        Forms\Components\TextInput::make('unit')
                            ->label('Единица измерения')
                            ->maxLength(50),
                    ])->columns(2),

                Forms\Components\Section::make('Описание')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('manufacturer')
                            ->label('Производитель')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('model')
                            ->label('Модель')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('supplier')
                            ->label('Поставщик')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активный')
                            ->default(true),

                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категория')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Склад')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->sortable()
                    ->color(fn($record) => $record->quantity <= $record->min_stock ? 'danger' : null),

                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Мин. кол-во')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Закупочная цена')
                    ->money('RUB')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('retail_price')
                    ->label('Розничная цена')
                    ->money('RUB')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('manufacturer')
                    ->label('Производитель')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активный')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Единица')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('Склад')
                    ->relationship('warehouse', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активный')
                    ->boolean()
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Доступен для заказа')
                    ->boolean()
                    ->native(false),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Низкий остаток')
                    ->query(fn(Builder $query): Builder => $query->whereRaw('quantity <= min_quantity')),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Нет в наличии')
                    ->query(fn(Builder $query): Builder => $query->where('quantity', 0)),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockItems::route('/'),
            'create' => Pages\CreateStockItem::route('/create'),
            'view' => Pages\ViewStockItem::route('/{record}'),
            'edit' => Pages\EditStockItem::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
