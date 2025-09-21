<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\RepairResource\Pages;
use App\Models\Repair;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RepairResource extends Resource
{
    protected static ?string $model = Repair::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Ремонты';

    protected static ?string $modelLabel = 'Ремонт';

    protected static ?string $pluralModelLabel = 'Ремонты';

    protected static ?string $navigationGroup = 'Ремонтная мастерская';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->label('Заказ')
                            ->relationship('order', 'order_number')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('order_number')
                                    ->label('Номер заказа')
                                    ->required()
                                    ->unique()
                                    ->default(fn() => 'REP-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT)),
                            ]),

                        Forms\Components\TextInput::make('price')
                            ->label('Стоимость ремонта')
                            ->numeric()
                            ->prefix('₽')
                            ->step(0.01)
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Статус ремонта')
                            ->options([
                                'pending' => 'Ожидает',
                                'diagnosis' => 'Диагностика',
                                'in_progress' => 'В работе',
                                'waiting_parts' => 'Ожидание запчастей',
                                'testing' => 'Тестирование',
                                'completed' => 'Завершен',
                                'cancelled' => 'Отменен',
                            ])
                            ->required()
                            ->default('pending'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Описание работ')
                    ->schema([
                        Forms\Components\Textarea::make('problem_description')
                            ->label('Описание проблемы')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('completed_works')
                            ->label('Выполненные работы')
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('comments')
                            ->label('Комментарии мастера')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Запчасти для ремонта')
                    ->schema([
                        Forms\Components\Repeater::make('parts')
                            ->label('Используемые запчасти')
                            ->schema([
                                Forms\Components\Select::make('stock_item_id')
                                    ->label('Запчасть')
                                    ->options(\App\Models\StockItem::active()->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            $stockItem = \App\Models\StockItem::find($state);
                                            if ($stockItem) {
                                                $set('unit_price', $stockItem->retail_price);
                                                $set('available_stock', $stockItem->quantity);
                                            }
                                        }
                                    }),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Количество')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $unitPrice = $get('unit_price');
                                        if ($state && $unitPrice) {
                                            $set('total_price', $state * $unitPrice);
                                        }
                                    }),

                                Forms\Components\TextInput::make('available_stock')
                                    ->label('Доступно на складе')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Цена за единицу')
                                    ->numeric()
                                    ->prefix('₽')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('total_price')
                                    ->label('Общая стоимость')
                                    ->numeric()
                                    ->prefix('₽')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\Textarea::make('description')
                                    ->label('Описание использования')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(5)
                            ->collapsible()
                            ->defaultItems(0)
                            ->addActionLabel('Добавить запчасть')
                            ->deleteAction(
                                fn($action) => $action->label('Удалить запчасть')
                            )
                            ->reorderable(false),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Медиафайлы')
                    ->schema([
                        Forms\Components\FileUpload::make('before_photos')
                            ->label('Фото до ремонта')
                            ->image()
                            ->multiple()
                            ->directory('repairs/before')
                            ->visibility('private')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('after_photos')
                            ->label('Фото после ремонта')
                            ->image()
                            ->multiple()
                            ->directory('repairs/after')
                            ->visibility('private')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Номер заказа')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('order.client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('problem_description')
                    ->label('Проблема')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Ожидает',
                        'diagnosis' => 'Диагностика',
                        'in_progress' => 'В работе',
                        'waiting_parts' => 'Ожидание запчастей',
                        'testing' => 'Тестирование',
                        'completed' => 'Завершен',
                        'cancelled' => 'Отменен',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'diagnosis' => 'info',
                        'in_progress' => 'warning',
                        'waiting_parts' => 'danger',
                        'testing' => 'purple',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label('Стоимость')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('before_photos')
                    ->label('Фото до')
                    ->boolean()
                    ->getStateUsing(fn(Repair $record): bool => $record->getMedia('before_photos')->count() > 0),

                Tables\Columns\IconColumn::make('after_photos')
                    ->label('Фото после')
                    ->boolean()
                    ->getStateUsing(fn(Repair $record): bool => $record->getMedia('after_photos')->count() > 0),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Ожидает',
                        'diagnosis' => 'Диагностика',
                        'in_progress' => 'В работе',
                        'waiting_parts' => 'Ожидание запчастей',
                        'testing' => 'Тестирование',
                        'completed' => 'Завершен',
                        'cancelled' => 'Отменен',
                    ]),

                Tables\Filters\Filter::make('has_photos')
                    ->label('С фото')
                    ->query(fn(Builder $query): Builder => $query->whereHas('media')),

                Tables\Filters\Filter::make('no_photos')
                    ->label('Без фото')
                    ->query(fn(Builder $query): Builder => $query->whereDoesntHave('media')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('change_status')
                    ->label('Изменить статус')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Новый статус')
                            ->options([
                                'pending' => 'Ожидает',
                                'diagnosis' => 'Диагностика',
                                'in_progress' => 'В работе',
                                'waiting_parts' => 'Ожидание запчастей',
                                'testing' => 'Тестирование',
                                'completed' => 'Завершен',
                                'cancelled' => 'Отменен',
                            ])
                            ->required(),
                    ])
                    ->action(function (Repair $record, array $data): void {
                        $record->update(['status' => $data['status']]);
                        \Filament\Notifications\Notification::make()
                            ->title('Статус обновлен')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListRepairs::route('/'),
            'create' => Pages\CreateRepair::route('/create'),
            'view' => Pages\ViewRepair::route('/{record}'),
            'edit' => Pages\EditRepair::route('/{record}/edit'),
        ];
    }
}
