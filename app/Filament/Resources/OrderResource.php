<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ActivityLogRelationManager;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Заказы';

    protected static ?string $modelLabel = 'Заказ';

    protected static ?string $pluralModelLabel = 'Заказы';

    protected static ?string $navigationGroup = 'Заказы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Клиент')
                            ->relationship('client', 'full_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('full_name')
                                    ->label('ФИО')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Телефон')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('+7 (999) 123-45-67')
                                    ->unique('clients', 'phone', ignoreRecord: true),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255)
                                    ->unique('clients', 'email', ignoreRecord: true)
                                    ->nullable()
                                    ->placeholder('email@example.com'),
                            ]),

                        Forms\Components\Select::make('branch_id')
                            ->label('Филиал')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('order_number')
                            ->label('Номер заказа')
                            ->required()
                            ->maxLength(255)
                            ->default(fn() => Order::generateOrderNumber()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Детали заказа')
                    ->schema([
                        Forms\Components\Select::make('service_type')
                            ->label('Тип услуги')
                            ->options(Order::getAvailableTypes())
                            ->required()
                            ->default(Order::TYPE_REPAIR),

                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options(Order::getAvailableStatuses())
                            ->required()
                            ->default(Order::STATUS_NEW),

                        Forms\Components\Select::make('urgency')
                            ->label('Срочность')
                            ->options(Order::getAvailableUrgencies())
                            ->default(Order::URGENCY_NORMAL),

                        Forms\Components\TextInput::make('estimated_price')
                            ->label('Ориентировочная цена')
                            ->numeric()
                            ->prefix('₽')
                            ->step(0.01),

                        Forms\Components\TextInput::make('actual_price')
                            ->label('Фактическая цена')
                            ->numeric()
                            ->prefix('₽')
                            ->step(0.01),

                        Forms\Components\Select::make('order_payment_type')
                            ->label('Тип оплаты')
                            ->options([
                                Order::PAYMENT_TYPE_PAID => 'Оплачен',
                                Order::PAYMENT_TYPE_WARRANTY => 'Гарантия',
                            ])
                            ->default(Order::PAYMENT_TYPE_PAID)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Оборудование')
                    ->schema([
                        Forms\Components\TextInput::make('equipment_name')
                            ->label('Название оборудования')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('equipment_serial_number')
                            ->label('Серийный номер')
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Доставка')
                    ->schema([
                        Forms\Components\Textarea::make('delivery_address')
                            ->label('Адрес доставки')
                            ->rows(2)
                            ->maxLength(65535)
                            ->nullable(),

                        Forms\Components\TextInput::make('delivery_cost')
                            ->label('Стоимость доставки')
                            ->numeric()
                            ->prefix('₽')
                            ->step(0.01)
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Описание')
                    ->schema([
                        Forms\Components\Textarea::make('problem_description')
                            ->label('Описание проблемы')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('internal_notes')
                            ->label('Внутренние заметки')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Ответственные')
                    ->schema([
                        Forms\Components\Select::make('manager_id')
                            ->label('Менеджер')
                            ->relationship('manager', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\Select::make('master_id')
                            ->label('Мастер')
                            ->relationship('master', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Дополнительно')
                    ->schema([
                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удален')
                            ->default(false),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Номер заказа')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Филиал')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Тип услуги')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Order::TYPE_REPAIR => 'primary',
                        Order::TYPE_SHARPENING => 'success',
                        Order::TYPE_DIAGNOSTIC => 'warning',
                        Order::TYPE_CONSULTATION => 'info',
                        Order::TYPE_MAINTENANCE => 'secondary',
                        Order::TYPE_WARRANTY => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => Order::getAvailableTypes()[$state] ?? $state),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Order::STATUS_NEW => 'primary',
                        Order::STATUS_CONSULTATION => 'warning',
                        Order::STATUS_DIAGNOSTIC => 'info',
                        Order::STATUS_IN_WORK => 'secondary',
                        Order::STATUS_WAITING_PARTS => 'danger',
                        Order::STATUS_READY => 'success',
                        Order::STATUS_ISSUED => 'gray',
                        Order::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => Order::getAvailableStatuses()[$state] ?? $state),

                Tables\Columns\TextColumn::make('urgency')
                    ->label('Срочность')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Order::URGENCY_NORMAL => 'primary',
                        Order::URGENCY_URGENT => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => Order::getAvailableUrgencies()[$state] ?? $state),

                Tables\Columns\TextColumn::make('estimated_price')
                    ->label('Ориентировочная цена')
                    ->money('RUB')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('actual_price')
                    ->label('Фактическая цена')
                    ->money('RUB')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('master.name')
                    ->label('Мастер')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('equipment_name')
                    ->label('Оборудование')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('order_payment_type')
                    ->label('Тип оплаты')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Order::PAYMENT_TYPE_PAID => 'success',
                        Order::PAYMENT_TYPE_WARRANTY => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        Order::PAYMENT_TYPE_PAID => 'Оплачен',
                        Order::PAYMENT_TYPE_WARRANTY => 'Гарантия',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Удален')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options(Order::getAvailableStatuses()),

                Tables\Filters\SelectFilter::make('service_type')
                    ->label('Тип услуги')
                    ->options(Order::getAvailableTypes()),

                Tables\Filters\SelectFilter::make('master_id')
                    ->label('Мастер')
                    ->relationship('master', 'name'),

                Tables\Filters\SelectFilter::make('urgency')
                    ->label('Срочность')
                    ->options(Order::getAvailableUrgencies()),

                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Филиал')
                    ->relationship('branch', 'name'),

                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Удаленные')
                    ->placeholder('Все заказы')
                    ->trueLabel('Только удаленные')
                    ->falseLabel('Только активные'),
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
                            ->options(Order::getAvailableStatuses())
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
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
                    Tables\Actions\BulkAction::make('mark_deleted')
                        ->label('Пометить как удаленные')
                        ->icon('heroicon-o-trash')
                        ->action(function ($records): void {
                            $records->each->update(['is_deleted' => true]);
                            \Filament\Notifications\Notification::make()
                                ->title('Заказы помечены как удаленные')
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
            ActivityLogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
