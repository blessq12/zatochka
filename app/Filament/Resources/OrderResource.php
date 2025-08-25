<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
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
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    // protected static ?string $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Заказы';
    protected static ?int $navigationSort = 2;
    protected static ?string $breadcrumb = 'Заказы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('Номер заказа')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('client_id')
                            ->label('Клиент')
                            ->relationship('client', 'full_name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('full_name')
                                    ->label('ФИО')
                                    ->required(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Телефон')
                                    ->tel(),
                                Forms\Components\TextInput::make('telegram')
                                    ->label('Telegram'),
                            ])
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options(Order::getStatusOptions())
                            ->required(),
                        Forms\Components\Select::make('service_type')
                            ->label('Тип услуги')
                            ->options(Order::getServiceTypeOptions())
                            ->required(),
                        Forms\Components\Select::make('equipment_type')
                            ->label('Тип оборудования')
                            ->options(Order::getEquipmentTypeOptions())
                            ->required(),
                        Forms\Components\TextInput::make('equipment_name')
                            ->label('Название оборудования')
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Детали заказа')
                    ->schema([
                        Forms\Components\Textarea::make('problem_description')
                            ->label('Описание проблемы')
                            ->rows(3),
                        Forms\Components\Textarea::make('work_description')
                            ->label('Описание работ')
                            ->rows(3),
                        Forms\Components\TextInput::make('total_tools_count')
                            ->label('Количество инструментов')
                            ->numeric()
                            ->default(1),
                        Forms\Components\Toggle::make('needs_consultation')
                            ->label('Нужна консультация')
                            ->default(false),
                        Forms\Components\Toggle::make('needs_delivery')
                            ->label('Нужна доставка')
                            ->default(false),
                        Forms\Components\Textarea::make('delivery_address')
                            ->label('Адрес доставки')
                            ->rows(2)
                            ->visible(fn($get) => $get('needs_delivery')),
                    ])->columns(2),

                Forms\Components\Section::make('Финансы')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Сумма')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('cost_price')
                            ->label('Себестоимость')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('profit')
                            ->label('Прибыль')
                            ->numeric()
                            ->required(),
                        Forms\Components\Select::make('payment_type')
                            ->label('Тип оплаты')
                            ->options(Order::getPaymentTypeOptions()),
                        Forms\Components\Select::make('delivery_type')
                            ->label('Тип доставки')
                            ->options(Order::getDeliveryTypeOptions()),
                        Forms\Components\TextInput::make('discount_percent')
                            ->label('Скидка (%)')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('№ заказа')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('ФИО клиента')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.phone')
                    ->label('Телефон')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('client.telegram')
                    ->label('Telegram')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('service_type')
                    ->label('Услуга')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'repair' => 'danger',
                        'maintenance' => 'warning',
                        'diagnostic' => 'info',
                        'consultation' => 'info',
                        'parts_replacement' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => Order::getServiceTypeOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('equipment_type')
                    ->label('Оборудование')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => Order::getEquipmentTypeOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('equipment_name')
                    ->label('Название оборудования')
                    ->searchable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('total_tools_count')
                    ->label('Кол-во инструментов')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('client.delivery_address')
                    ->label('Адрес доставки')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('is_paid')
                    ->label('Оплата')
                    ->badge()
                    ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Оплачен' : 'Не оплачен'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn(Order $record): string => $record->getStatusColor())
                    ->formatStateUsing(fn(string $state): string => Order::getStatusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options(Order::getStatusOptions()),
                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Оплата')
                    ->placeholder('Все')
                    ->trueLabel('Оплаченные')
                    ->falseLabel('Неоплаченные'),
                Tables\Filters\SelectFilter::make('service_type')
                    ->label('Тип услуги')
                    ->options(Order::getServiceTypeOptions()),
                Tables\Filters\SelectFilter::make('equipment_type')
                    ->label('Тип оборудования')
                    ->options(Order::getEquipmentTypeOptions()),
                Tables\Filters\SelectFilter::make('payment_type')
                    ->label('Тип оплаты')
                    ->options(Order::getPaymentTypeOptions()),
                Tables\Filters\SelectFilter::make('delivery_type')
                    ->label('Тип доставки')
                    ->options(Order::getDeliveryTypeOptions()),
                Tables\Filters\Filter::make('client_frequency')
                    ->label('Частота обращений клиента')
                    ->form([
                        \Filament\Forms\Components\Select::make('frequency')
                            ->label('Количество заказов в месяц')
                            ->options([
                                '1' => '1 раз в месяц',
                                '2' => '2 раза в месяц',
                                '3' => '3 раза в месяц',
                                '4+' => '4+ раза в месяц',
                            ])
                            ->required(),
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['frequency'])) {
                            $frequency = $data['frequency'];
                            $monthStart = now()->startOfMonth();
                            $monthEnd = now()->endOfMonth();

                            if ($frequency === '4+') {
                                return $query->whereHas('client', function ($q) use ($monthStart, $monthEnd) {
                                    $q->whereHas('orders', function ($orderQuery) use ($monthStart, $monthEnd) {
                                        $orderQuery->whereBetween('created_at', [$monthStart, $monthEnd]);
                                    }, '>=', 4);
                                });
                            } else {
                                return $query->whereHas('client', function ($q) use ($monthStart, $monthEnd, $frequency) {
                                    $q->whereHas('orders', function ($orderQuery) use ($monthStart, $monthEnd) {
                                        $orderQuery->whereBetween('created_at', [$monthStart, $monthEnd]);
                                    }, '=', (int)$frequency);
                                });
                            }
                        }
                        return $query;
                    }),
                Tables\Filters\Filter::make('date_range')
                    ->label('Дата обращения')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('От'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('До'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn($query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn($query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Tables\Filters\SelectFilter::make('client')
                    ->label('Клиент')
                    ->relationship('client', 'full_name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Просмотр'),
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('confirm')
                        ->label('Подтвердить')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->visible(fn(Order $record): bool => $record->status === 'new')
                        ->action(function (Order $record): void {
                            $record->confirm();
                        }),
                    Tables\Actions\Action::make('assign_courier')
                        ->label('Передать курьеру')
                        ->icon('heroicon-m-truck')
                        ->color('warning')
                        ->visible(fn(Order $record): bool => in_array($record->status, ['new', 'confirmed']))
                        ->action(function (Order $record): void {
                            $record->assignToCourier();
                        }),
                    Tables\Actions\Action::make('assign_master')
                        ->label('Передать мастеру')
                        ->icon('heroicon-m-wrench-screwdriver')
                        ->color('warning')
                        ->visible(fn(Order $record): bool => in_array($record->status, ['confirmed', 'courier_pickup']))
                        ->action(function (Order $record): void {
                            $record->assignToMaster();
                        }),
                    Tables\Actions\Action::make('start_work')
                        ->label('Начать работу')
                        ->icon('heroicon-m-play')
                        ->color('warning')
                        ->visible(fn(Order $record): bool => in_array($record->status, ['master_received', 'confirmed']))
                        ->action(function (Order $record): void {
                            $record->startWork();
                        }),
                    Tables\Actions\Action::make('complete_work')
                        ->label('Завершить работу')
                        ->icon('heroicon-m-check')
                        ->color('success')
                        ->visible(fn(Order $record): bool => in_array($record->status, ['in_progress', 'master_received']))
                        ->action(function (Order $record): void {
                            $record->completeWork();
                        }),
                    Tables\Actions\Action::make('mark_ready')
                        ->label('Готов к выдаче')
                        ->icon('heroicon-m-gift')
                        ->color('success')
                        ->visible(fn(Order $record): bool => in_array($record->status, ['work_completed', 'in_progress']))
                        ->action(function (Order $record): void {
                            $record->markAsReady();
                        }),
                    Tables\Actions\Action::make('assign_delivery')
                        ->label('Передать на доставку')
                        ->icon('heroicon-m-truck')
                        ->color('warning')
                        ->visible(fn(Order $record): bool => in_array($record->status, ['work_completed', 'ready_for_pickup']))
                        ->action(function (Order $record): void {
                            $record->assignToDeliveryCourier();
                        }),
                    Tables\Actions\Action::make('mark_delivered')
                        ->label('Доставлен')
                        ->icon('heroicon-m-home')
                        ->color('info')
                        ->visible(fn(Order $record): bool => in_array($record->status, ['courier_delivery', 'ready_for_pickup']))
                        ->action(function (Order $record): void {
                            $record->markAsDelivered();
                        }),
                    Tables\Actions\Action::make('receive_payment')
                        ->label('Получена оплата')
                        ->icon('heroicon-m-banknotes')
                        ->color('success')
                        ->visible(fn(Order $record): bool => in_array($record->status, ['delivered', 'ready_for_pickup']))
                        ->action(function (Order $record): void {
                            $record->receivePayment();
                        }),
                    Tables\Actions\Action::make('close')
                        ->label('Закрыть заказ')
                        ->icon('heroicon-m-lock-closed')
                        ->color('success')
                        ->visible(fn(Order $record): bool => in_array($record->status, ['payment_received', 'delivered']))
                        ->action(function (Order $record): void {
                            $record->close();
                        }),

                    Tables\Actions\Action::make('cancel')
                        ->label('Отменить')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->visible(fn(Order $record): bool => !in_array($record->status, ['closed', 'cancelled']))
                        ->action(function (Order $record): void {
                            $record->cancel();
                        }),
                ])
                    ->label('Действия')
                    ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderToolsRelationManager::class,
            RelationManagers\RepairsRelationManager::class,
            RelationManagers\NotificationsRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
