<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\StockItemResource\Pages;
use App\Filament\Resources\Manager\StockItemResource\RelationManagers;
use App\Models\StockItem;
use App\Models\StockCategory;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockItemResource extends Resource
{
    protected static ?string $model = StockItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Склад';
    protected static ?string $pluralLabel = 'Товары';
    protected static ?string $modelLabel = 'Товар';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название товара')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('sku')
                            ->label('SKU/Артикул')
                            ->required()
                            ->maxLength(100)
                            ->unique(StockItem::class, 'sku', ignoreRecord: true)
                            ->helperText('Уникальный код товара'),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Классификация')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Категория товара')
                            ->options(function () {
                                return \App\Models\StockCategory::with('warehouse')
                                    ->get()
                                    ->mapWithKeys(function ($category) {
                                        return [$category->id => $category->name . ' (' . $category->warehouse->name . ')'];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Категория определяет склад для товара')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $category = \App\Models\StockCategory::with('warehouse')->find($state);
                                    if ($category) {
                                        $set('warehouse_id', $category->warehouse_id);
                                    }
                                }
                            }),

                        Forms\Components\Hidden::make('warehouse_id'),
                    ]),

                Forms\Components\Section::make('Цены')
                    ->schema([
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
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Остатки')
                    ->schema([
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
                            ->minValue(0)
                            ->helperText('При достижении этого количества будет показано предупреждение'),

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
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Дополнительная информация')
                    ->schema([
                        Forms\Components\TextInput::make('supplier')
                            ->label('Поставщик')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('manufacturer')
                            ->label('Производитель')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('model')
                            ->label('Модель')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->helperText('Неактивные товары не отображаются в выборе'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категория')
                    ->badge()
                    ->color(fn($record) => $record->category?->color ?? 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Склад')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Остаток')
                    ->numeric()
                    ->sortable()
                    ->color(fn($record) => $record->isLowStock() ? 'danger' : 'success')
                    ->formatStateUsing(fn($state, $record) => $state . ' ' . $record->unit),

                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Мин. остаток')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Закупочная')
                    ->money('RUB')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('retail_price')
                    ->label('Розничная')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier')
                    ->label('Поставщик')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('manufacturer')
                    ->label('Производитель')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('Склад')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активные')
                    ->placeholder('Все товары')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Низкий остаток')
                    ->query(fn(Builder $query): Builder => $query->lowStock()),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Нет в наличии')
                    ->query(fn(Builder $query): Builder => $query->where('quantity', '<=', 0)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('adjust_stock')
                    ->label('Изменить остаток')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('new_quantity')
                            ->label('Новое количество')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\Textarea::make('reason')
                            ->label('Причина изменения')
                            ->rows(2),
                    ])
                    ->action(function (StockItem $record, array $data): void {
                        $record->adjustStock($data['new_quantity'], $data['reason'] ?? '');
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Активировать')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn($records) => $records->each->activate())
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Деактивировать')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn($records) => $records->each->deactivate())
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'edit' => Pages\EditStockItem::route('/{record}/edit'),
        ];
    }
}
