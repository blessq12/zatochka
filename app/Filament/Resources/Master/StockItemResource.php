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

    protected static ?string $navigationLabel = 'Запчасти';

    protected static ?string $modelLabel = 'Запчасть';

    protected static ?string $pluralModelLabel = 'Запчасти';

    protected static ?string $navigationGroup = 'Справочники';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->disabled(),

                        Forms\Components\TextInput::make('sku')
                            ->label('Артикул')
                            ->disabled(),

                        Forms\Components\Select::make('category_id')
                            ->label('Категория')
                            ->relationship('category', 'name')
                            ->disabled(),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Цены и остатки')
                    ->schema([
                        Forms\Components\TextInput::make('purchase_price')
                            ->label('Закупочная цена')
                            ->numeric()
                            ->prefix('₽')
                            ->disabled(),

                        Forms\Components\TextInput::make('retail_price')
                            ->label('Розничная цена')
                            ->numeric()
                            ->prefix('₽')
                            ->disabled(),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('min_stock')
                            ->label('Минимальный остаток')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('unit')
                            ->label('Единица измерения')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Дополнительная информация')
                    ->schema([
                        Forms\Components\TextInput::make('supplier')
                            ->label('Поставщик')
                            ->disabled(),

                        Forms\Components\TextInput::make('manufacturer')
                            ->label('Производитель')
                            ->disabled(),

                        Forms\Components\TextInput::make('model')
                            ->label('Модель')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активно')
                            ->disabled(),

                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удалено')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('sku')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категория')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Остаток')
                    ->sortable()
                    ->color(fn($state, $record) => $state <= $record->min_stock ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Мин. остаток')
                    ->sortable(),

                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Закупочная цена')
                    ->money('RUB')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('retail_price')
                    ->label('Розничная цена')
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
                    ->label('Активно')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Добавлен')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->placeholder('Все запчасти')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Низкие запасы')
                    ->query(fn(Builder $query): Builder => $query->whereColumn('quantity', '<=', 'min_stock')),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Нет в наличии')
                    ->query(fn(Builder $query): Builder => $query->where('quantity', 0)),

                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Удалено')
                    ->placeholder('Все запчасти')
                    ->trueLabel('Только удаленные')
                    ->falseLabel('Только активные'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('use_in_repair')
                    ->label('Использовать в ремонте')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Количество для списания')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(fn($record) => $record->quantity),
                        Forms\Components\Textarea::make('description')
                            ->label('Описание использования')
                            ->required(),
                    ])
                    ->action(function (StockItem $record, array $data): void {
                        // Здесь будет логика списания запчастей
                        \Filament\Notifications\Notification::make()
                            ->title('Запчасть списана')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(StockItem $record): bool => $record->quantity > 0),
            ])
            ->bulkActions([])
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
            'view' => Pages\ViewStockItem::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
