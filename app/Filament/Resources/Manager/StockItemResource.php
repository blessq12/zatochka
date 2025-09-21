<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\StockItemResource\Pages;
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

    protected static ?string $navigationGroup = 'Инвентарь';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Категория')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('sku')
                            ->label('Артикул')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Цены и остатки')
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

                        Forms\Components\TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\TextInput::make('min_stock')
                            ->label('Минимальный остаток')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('При достижении этого уровня будет предупреждение'),

                        Forms\Components\TextInput::make('unit')
                            ->label('Единица измерения')
                            ->maxLength(50)
                            ->default('шт')
                            ->helperText('шт, кг, м, л и т.д.'),
                    ])
                    ->columns(2),

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
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),

                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удалена')
                            ->default(false),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категория')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(StockItem $record): string => $record->category?->getDisplayColor() ?? 'gray'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Остаток')
                    ->sortable()
                    ->formatStateUsing(function (int $state, StockItem $record): string {
                        $unit = $record->unit ?? 'шт';
                        $color = match (true) {
                            $record->isOutOfStock() => 'danger',
                            $record->isLowStock() => 'warning',
                            default => 'success'
                        };

                        return "<span class='badge badge-{$color}'>{$state} {$unit}</span>";
                    })
                    ->html(),

                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Мин. остаток')
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

                Tables\Columns\TextColumn::make('total_value')
                    ->label('Общая стоимость')
                    ->getStateUsing(fn(StockItem $record): float => $record->getTotalValue())
                    ->money('RUB')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('supplier')
                    ->label('Поставщик')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('manufacturer')
                    ->label('Производитель')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('is_active')
                    ->label('Статус')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Активна' : 'Неактивна'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Удалена')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->placeholder('Все товары')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),

                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Удаленные')
                    ->placeholder('Все товары')
                    ->trueLabel('Только удаленные')
                    ->falseLabel('Только активные'),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Низкие запасы')
                    ->query(fn(Builder $query): Builder => $query->whereRaw('quantity <= min_stock')),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Нет в наличии')
                    ->query(fn(Builder $query): Builder => $query->where('quantity', '<=', 0)),

                Tables\Filters\Filter::make('has_supplier')
                    ->label('С поставщиком')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('supplier')),

                Tables\Filters\Filter::make('has_manufacturer')
                    ->label('С производителем')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('manufacturer')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('adjust_stock')
                    ->label('Корректировка остатка')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('new_quantity')
                            ->label('Новое количество')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\Textarea::make('reason')
                            ->label('Причина')
                            ->rows(2),
                    ])
                    ->action(function (StockItem $record, array $data): void {
                        $record->adjustStock($data['new_quantity'], $data['reason'] ?? '');
                        \Filament\Notifications\Notification::make()
                            ->title('Остаток скорректирован')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('add_stock')
                    ->label('Добавить')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Количество для добавления')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                    ])
                    ->action(function (StockItem $record, array $data): void {
                        $record->addStock($data['amount']);
                        \Filament\Notifications\Notification::make()
                            ->title('Товар добавлен на склад')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_deleted')
                        ->label('Пометить как удаленные')
                        ->icon('heroicon-o-trash')
                        ->action(function ($records): void {
                            $records->each->update(['is_deleted' => true]);
                            \Filament\Notifications\Notification::make()
                                ->title('Товары помечены как удаленные')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Активировать')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records): void {
                            $records->each->activate();
                            \Filament\Notifications\Notification::make()
                                ->title('Товары активированы')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Деактивировать')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function ($records): void {
                            $records->each->deactivate();
                            \Filament\Notifications\Notification::make()
                                ->title('Товары деактивированы')
                                ->warning()
                                ->send();
                        }),
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
            'view' => Pages\ViewStockItem::route('/{record}'),
            'edit' => Pages\EditStockItem::route('/{record}/edit'),
        ];
    }
}
