<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ActivityLogRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\OrderWorksRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\OrderMaterialsRelationManager;
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
                            ->required()
                            ->default(fn () => \App\Models\Branch::first()?->id),

                        Forms\Components\TextInput::make('order_number')
                            ->label('Номер заказа')
                            ->disabled(fn ($operation) => $operation === 'create')
                            ->dehydrated()
                            ->default(fn ($operation) => $operation === 'create' ? 'Автоматически' : null)
                            ->helperText(fn ($operation) => $operation === 'create' 
                                ? 'Номер заказа будет сгенерирован автоматически при сохранении' 
                                : 'Номер заказа нельзя изменить'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Детали заказа')
                    ->schema([
                        Forms\Components\Select::make('service_type')
                            ->label('Тип услуги')
                            ->options([
                                Order::TYPE_SHARPENING => 'Заточка',
                                Order::TYPE_REPAIR => 'Ремонт',
                                Order::TYPE_DIAGNOSTIC => 'Диагностика',
                            ])
                            ->required()
                            ->default(Order::TYPE_REPAIR)
                            ->live(),

                        Forms\Components\Select::make('order_payment_type')
                            ->label('Вид заказа')
                            ->options([
                                Order::PAYMENT_TYPE_PAID => 'Платный',
                                Order::PAYMENT_TYPE_WARRANTY => 'Гарантийный',
                            ])
                            ->default(Order::PAYMENT_TYPE_PAID)
                            ->required()
                            ->live(),

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
                            ->step(0.01)
                            ->visible(fn ($get) => $get('order_payment_type') === Order::PAYMENT_TYPE_PAID),

                        Forms\Components\TextInput::make('actual_price')
                            ->label('Фактическая цена')
                            ->numeric()
                            ->prefix('₽')
                            ->step(0.01)
                            ->visible(fn ($get) => $get('order_payment_type') === Order::PAYMENT_TYPE_PAID),
                    ])
                    ->columns(2),

                // Секция для заточки
                Forms\Components\Section::make('Инструменты для заточки')
                    ->schema([
                        Forms\Components\Repeater::make('tools')
                            ->label('Инструменты')
                            ->relationship('tools')
                            ->schema([
                                Forms\Components\TextInput::make('tool_type')
                                    ->label('Тип инструмента')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Например: ножницы, кусачки, ножи'),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Количество')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->helperText('Количество инструментов этого типа'),

                                Forms\Components\Textarea::make('description')
                                    ->label('Описание')
                                    ->rows(2)
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                ($state['tool_type'] ?? 'Инструмент') . 
                                (isset($state['quantity']) && $state['quantity'] > 1 ? " ({$state['quantity']} шт.)" : '')
                            ),
                    ])
                    ->visible(fn ($get) => $get('service_type') === Order::TYPE_SHARPENING),

                // Секция для ремонта и диагностики
                Forms\Components\Section::make('Оборудование')
                    ->schema([
                        Forms\Components\Select::make('equipment_id')
                            ->label('Оборудование')
                            ->relationship('equipment', 'name')
                            ->searchable()
                            ->preload()
                            ->required(fn ($get) => in_array($get('service_type'), [Order::TYPE_REPAIR, Order::TYPE_DIAGNOSTIC]))
                            ->getSearchResultsUsing(fn (string $search) => \App\Models\Equipment::query()
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('serial_number', 'like', "%{$search}%")
                                ->orWhere('brand', 'like', "%{$search}%")
                                ->orWhere('model', 'like', "%{$search}%")
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn ($equipment) => [
                                    $equipment->id => $equipment->full_name . ($equipment->serial_number ? ' (SN: ' . $equipment->serial_number . ')' : '')
                                ]))
                            ->getOptionLabelUsing(fn ($value): ?string => \App\Models\Equipment::find($value)?->full_name)
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Название оборудования')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('type')
                                    ->label('Тип оборудования')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('serial_number')
                                    ->label('Серийный номер')
                                    ->maxLength(255)
                                    ->unique('equipment', 'serial_number'),
                                Forms\Components\TextInput::make('brand')
                                    ->label('Производитель/Бренд')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('model')
                                    ->label('Модель')
                                    ->maxLength(255),
                                Forms\Components\Select::make('client_id')
                                    ->label('Владелец')
                                    ->relationship('client', 'full_name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                            ])
                            ->helperText('Выберите существующее оборудование или создайте новое')
                            ->visible(fn ($get) => in_array($get('service_type'), [Order::TYPE_REPAIR, Order::TYPE_DIAGNOSTIC])),
                    ])
                    ->visible(fn ($get) => in_array($get('service_type'), [Order::TYPE_REPAIR, Order::TYPE_DIAGNOSTIC])),

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
                            ->default(fn () => \Illuminate\Support\Facades\Auth::id())
                            ->required(),

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

                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('Оборудование')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn ($record) => $record && in_array($record->service_type, [Order::TYPE_REPAIR, Order::TYPE_DIAGNOSTIC])),

                Tables\Columns\TextColumn::make('tools_summary')
                    ->label('Инструменты')
                    ->getStateUsing(function ($record) {
                        if ($record->service_type !== Order::TYPE_SHARPENING) {
                            return null;
                        }
                        $tools = $record->tools;
                        if ($tools->isEmpty()) {
                            return '—';
                        }
                        return $tools->map(fn($tool) => $tool->tool_type . ($tool->quantity > 1 ? " ({$tool->quantity})" : ''))->join(', ');
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn ($record) => $record && $record->service_type === Order::TYPE_SHARPENING),

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
            OrderWorksRelationManager::class,
            OrderMaterialsRelationManager::class,
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
