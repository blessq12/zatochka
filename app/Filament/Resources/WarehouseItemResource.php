<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseItemResource\Pages;
use App\Filament\Resources\WarehouseItemResource\RelationManagers;
use App\Models\WarehouseItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarehouseItemResource extends Resource
{
    protected static ?string $model = WarehouseItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Товары склада';

    protected static ?string $modelLabel = 'Товар';

    protected static ?string $pluralModelLabel = 'Товары склада';

    protected static ?string $navigationGroup = 'Склад';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('warehouse_category_id')
                            ->label('Категория')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Название категории')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->label('Описание')
                                    ->rows(3),
                            ]),

                        Forms\Components\TextInput::make('name')
                            ->label('Название товара')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('article')
                            ->label('Артикул')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Оставьте пустым для автоматической генерации. Уникальный артикул товара'),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Складской учет')
                    ->schema([
                        Forms\Components\TextInput::make('unit')
                            ->label('Единица измерения')
                            ->default('шт')
                            ->maxLength(20)
                            ->required()
                            ->helperText('шт, кг, м, л и т.д.'),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Количество на складе')
                            ->numeric()
                            ->step(0.001)
                            ->default(0)
                            ->required()
                            ->prefix('кол-во'),

                        Forms\Components\TextInput::make('min_quantity')
                            ->label('Минимальное количество')
                            ->numeric()
                            ->step(0.001)
                            ->default(0)
                            ->helperText('Для уведомлений о низком остатке')
                            ->prefix('мин'),

                        Forms\Components\TextInput::make('purchase_price')
                            ->label('Цена закупки')
                            ->numeric()
                            ->step(0.01)
                            ->default(0)
                            ->prefix('₽')
                            ->helperText('Цена закупки товара'),

                        Forms\Components\TextInput::make('price')
                            ->label('Цена за единицу')
                            ->numeric()
                            ->step(0.01)
                            ->default(0)
                            ->prefix('₽')
                            ->required()
                            ->helperText('Розничная цена для продажи в заказах'),

                        Forms\Components\TextInput::make('location')
                            ->label('Местоположение на складе')
                            ->maxLength(255)
                            ->helperText('Полка, стеллаж и т.д.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Дополнительно')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категория')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('article')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Количество')
                    ->numeric(
                        decimalPlaces: 3,
                    )
                    ->suffix(fn($record) => ' ' . $record->unit)
                    ->sortable()
                    ->color(fn($record) => $record->isLowStock() ? 'danger' : null),

                Tables\Columns\TextColumn::make('min_quantity')
                    ->label('Мин. кол-во')
                    ->numeric(
                        decimalPlaces: 3,
                    )
                    ->suffix(fn($record) => ' ' . $record->unit)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Местоположение')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активные')
                    ->placeholder('Все товары')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Низкий остаток')
                    ->query(fn($query) => $query->lowStock()),

                Tables\Filters\Filter::make('in_stock')
                    ->label('В наличии')
                    ->query(fn($query) => $query->inStock()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton()->tooltip('Просмотр'),
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Редактировать'),
                Tables\Actions\Action::make('adjust_quantity')
                    ->iconButton()
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->tooltip('Изменить количество')
                    ->form([
                        Forms\Components\TextInput::make('quantity_change')
                            ->label('Изменение количества')
                            ->numeric()
                            ->step(0.001)
                            ->required()
                            ->helperText('Положительное число для добавления, отрицательное для списания'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Примечание')
                            ->rows(2),
                    ])
                    ->action(function (WarehouseItem $record, array $data): void {
                        $change = (float) $data['quantity_change'];
                        $record->quantity += $change;

                        if ($record->quantity < 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('Ошибка')
                                ->body('Недостаточно товара на складе')
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->save();

                        \Filament\Notifications\Notification::make()
                            ->title('Количество обновлено')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()->iconButton()->tooltip('Удалить'),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export')
                        ->label('Экспорт')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            // TODO: Реализовать экспорт
                            \Filament\Notifications\Notification::make()
                                ->title('Экспорт выполнен')
                                ->success()
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
            'index' => Pages\ListWarehouseItems::route('/'),
            'create' => Pages\CreateWarehouseItem::route('/create'),
            'edit' => Pages\EditWarehouseItem::route('/{record}/edit'),
        ];
    }
}
