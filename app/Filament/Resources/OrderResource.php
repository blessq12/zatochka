<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ActivityLogRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\OrderWorksRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\OrderMaterialsRelationManager;
use App\Dictionaries\ToolTypeDictionary;
use App\Models\Order;
use App\Services\Document\Factories\DocumentGeneratorFactory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
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
        $clientCreateForm = [
            Forms\Components\TextInput::make('full_name')->label('ФИО')->required()->maxLength(255),
            Forms\Components\TextInput::make('phone')->label('Телефон')->required()->maxLength(255)
                ->placeholder('+7 (999) 123-45-67')->unique('clients', 'phone', ignoreRecord: true),
            Forms\Components\TextInput::make('email')->label('Email')->email()->maxLength(255)
                ->unique('clients', 'email', ignoreRecord: true)->nullable()->placeholder('email@example.com'),
        ];

        $equipmentCreateForm = [
            Forms\Components\TextInput::make('name')->label('Название')->required()->maxLength(255),
            Forms\Components\TextInput::make('type')->label('Тип')->maxLength(255),
            Forms\Components\Repeater::make('serial_number')
                ->label('Серийные номера (части)')
                ->schema([
                    Forms\Components\TextInput::make('name')->label('Часть')->maxLength(255)->placeholder('блок, двигатель…'),
                    Forms\Components\TextInput::make('serial_number')->label('Серийный номер')->required()->maxLength(255),
                ])
                ->columns(2)
                ->defaultItems(0)
                ->addActionLabel('Добавить часть'),
            Forms\Components\TextInput::make('brand')->label('Бренд')->maxLength(255),
            Forms\Components\TextInput::make('model')->label('Модель')->maxLength(255),
            Forms\Components\Select::make('client_id')->label('Владелец')->relationship('client', 'full_name')->searchable()->preload()->nullable(),
        ];

        return $form
            ->schema([
                Forms\Components\Section::make('Заказ')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Placeholder::make('order_number')
                                    ->label('Номер заказа')
                                    ->content(fn($get, $record) => $record?->order_number ?? 'Будет сгенерирован при сохранении'),

                                Forms\Components\Placeholder::make('calculated_price')
                                    ->label('Стоимость')
                                    ->content(fn($get, $record) => $record ? number_format($record->calculated_price, 2, ',', ' ') . ' ₽' : '0 ₽')
                                    ->visible(fn($get) => $get('order_payment_type') === Order::PAYMENT_TYPE_PAID),

                                Forms\Components\Placeholder::make('needs_delivery_display')
                                    ->label('Доставка')
                                    ->content(fn($get) => $get('needs_delivery') ? 'Нужна' : 'Не нужна'),

                                Forms\Components\Select::make('client_id')
                                    ->label('Клиент')
                                    ->relationship('client', 'full_name')
                                    ->searchable()->preload()->required()
                                    ->createOptionForm($clientCreateForm),

                                Forms\Components\Select::make('branch_id')
                                    ->label('Филиал')
                                    ->relationship('branch', 'name')
                                    ->searchable()->preload()->required()
                                    ->default(fn() => \App\Models\Branch::first()?->id),

                                Forms\Components\Select::make('service_type')
                                    ->label('Тип услуги')
                                    ->options([
                                        Order::TYPE_SHARPENING => 'Заточка',
                                        Order::TYPE_REPAIR => 'Ремонт',
                                        Order::TYPE_DIAGNOSTIC => 'Диагностика',
                                    ])
                                    ->required()->default(Order::TYPE_REPAIR)->live(),

                                Forms\Components\Select::make('status')
                                    ->label('Статус')
                                    ->options(Order::getAvailableStatuses())
                                    ->required()->default(Order::STATUS_NEW),

                                Forms\Components\Select::make('order_payment_type')
                                    ->label('Вид')
                                    ->options([
                                        Order::PAYMENT_TYPE_PAID => 'Платный',
                                        Order::PAYMENT_TYPE_WARRANTY => 'Гарантийный',
                                    ])
                                    ->default(Order::PAYMENT_TYPE_PAID)->required()->live(),

                                Forms\Components\Select::make('urgency')
                                    ->label('Срочность')
                                    ->options(Order::getAvailableUrgencies())
                                    ->default(Order::URGENCY_NORMAL),

                                Forms\Components\Select::make('manager_id')
                                    ->label('Менеджер')
                                    ->relationship('manager', 'name')
                                    ->searchable()->preload()
                                    ->default(fn() => \Illuminate\Support\Facades\Auth::id())
                                    ->required(),

                                Forms\Components\Select::make('master_id')
                                    ->label('Мастер')
                                    ->relationship('master', 'name')
                                    ->searchable()->preload()->nullable(),
                            ]),
                    ]),

                Forms\Components\Section::make('Предмет работы')
                    ->description('Инструменты для заточки или оборудование для ремонта')
                    ->schema([
                        Forms\Components\Repeater::make('tools')
                            ->label('Инструменты')
                            ->relationship('tools')
                            ->schema([
                                Forms\Components\Select::make('tool_type')
                                    ->label('Тип')
                                    ->options(ToolTypeDictionary::getLabels())
                                    ->required()
                                    ->searchable(),
                                Forms\Components\TextInput::make('quantity')->label('Кол-во')->numeric()->required()->minValue(1)->default(1),
                                Forms\Components\Textarea::make('description')->label('Описание')->rows(2)->maxLength(65535)->columnSpanFull(),
                            ])
                            ->columns(2)->defaultItems(1)->minItems(1)->collapsible()
                            ->itemLabel(fn(array $state): ?string => (ToolTypeDictionary::getLabel($state['tool_type'] ?? null) ?: 'Инструмент') . (isset($state['quantity']) && $state['quantity'] > 1 ? " ({$state['quantity']})" : ''))
                            ->visible(fn($get) => $get('service_type') === Order::TYPE_SHARPENING),
                        Forms\Components\Select::make('equipment_id')
                            ->label('Оборудование')
                            ->relationship('equipment', 'name')
                            ->searchable()->preload()
                            ->required(fn($get) => in_array($get('service_type'), [Order::TYPE_REPAIR, Order::TYPE_DIAGNOSTIC]))
                            ->getSearchResultsUsing(fn(string $search) => \App\Models\Equipment::query()
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('serial_number', 'like', "%{$search}%")
                                ->orWhere('brand', 'like', "%{$search}%")
                                ->orWhere('model', 'like', "%{$search}%")
                                ->limit(50)->get()
                                ->mapWithKeys(fn($e) => [$e->id => $e->full_name . ($e->serial_numbers_display ? ' (' . $e->serial_numbers_display . ')' : '')]))
                            ->getOptionLabelUsing(function ($value): ?string {
                                $e = \App\Models\Equipment::find($value);
                                return $e ? $e->full_name . ($e->serial_numbers_display ? ' — ' . $e->serial_numbers_display : '') : null;
                            })
                            ->createOptionForm($equipmentCreateForm)
                            ->visible(fn($get) => in_array($get('service_type'), [Order::TYPE_REPAIR, Order::TYPE_DIAGNOSTIC])),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed(fn($get) => !in_array($get('service_type') ?? '', [Order::TYPE_SHARPENING, Order::TYPE_REPAIR, Order::TYPE_DIAGNOSTIC])),

                Forms\Components\Section::make('Описание и заметки')
                    ->schema([
                        Forms\Components\Textarea::make('problem_description')->label('Описание проблемы')->rows(2)->maxLength(65535),
                        Forms\Components\Textarea::make('internal_notes')->label('Внутренние заметки')->rows(2)->maxLength(65535),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Forms\Components\Section::make('Доставка')
                    ->schema([
                        Forms\Components\Toggle::make('needs_delivery')->label('Нужна доставка')->default(false)->live(),
                        Forms\Components\Textarea::make('delivery_address')->label('Адрес')->rows(2)->maxLength(65535)->nullable()
                            ->visible(fn($get) => $get('needs_delivery')),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('№')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Статус')
                    ->options(Order::getAvailableStatuses())
                    ->selectablePlaceholder(false)
                    ->searchable()
                    ->sortable()
                    ->afterStateUpdated(function ($record, $state) {
                        \Filament\Notifications\Notification::make()
                            ->title('Статус обновлен')
                            ->success()
                            ->send();
                    }),

                Tables\Columns\IconColumn::make('service_type')
                    ->label('')
                    ->icon(fn(string $state): string => match ($state) {
                        Order::TYPE_SHARPENING => 'heroicon-o-scissors',
                        Order::TYPE_REPAIR => 'heroicon-o-wrench-screwdriver',
                        Order::TYPE_DIAGNOSTIC => 'heroicon-o-magnifying-glass',
                        Order::TYPE_CONSULTATION => 'heroicon-o-chat-bubble-left-right',
                        Order::TYPE_MAINTENANCE => 'heroicon-o-cog-6-tooth',
                        Order::TYPE_WARRANTY => 'heroicon-o-shield-check',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        Order::TYPE_REPAIR => 'primary',
                        Order::TYPE_SHARPENING => 'success',
                        Order::TYPE_DIAGNOSTIC => 'warning',
                        Order::TYPE_CONSULTATION => 'info',
                        Order::TYPE_MAINTENANCE => 'secondary',
                        Order::TYPE_WARRANTY => 'danger',
                        default => 'gray',
                    })
                    ->tooltip(fn(string $state): string => Order::getAvailableTypes()[$state] ?? $state),

                Tables\Columns\IconColumn::make('order_payment_type')
                    ->label('')
                    ->icon(fn(string $state): string => match ($state) {
                        Order::PAYMENT_TYPE_PAID => 'heroicon-o-banknotes',
                        Order::PAYMENT_TYPE_WARRANTY => 'heroicon-o-shield-check',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        Order::PAYMENT_TYPE_PAID => 'success',
                        Order::PAYMENT_TYPE_WARRANTY => 'warning',
                        default => 'gray',
                    })
                    ->tooltip(fn(string $state): string => match ($state) {
                        Order::PAYMENT_TYPE_PAID => 'Платный',
                        Order::PAYMENT_TYPE_WARRANTY => 'Гарантийный',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('urgency')
                    ->label('')
                    ->icon(fn(string $state): string => match ($state) {
                        Order::URGENCY_URGENT => 'heroicon-o-exclamation-triangle',
                        Order::URGENCY_NORMAL => 'heroicon-o-clock',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        Order::URGENCY_URGENT => 'danger',
                        Order::URGENCY_NORMAL => 'primary',
                        default => 'gray',
                    })
                    ->tooltip(fn(string $state): string => Order::getAvailableUrgencies()[$state] ?? $state),

                Tables\Columns\TextColumn::make('calculated_price')
                    ->label('Стоимость')
                    ->money('RUB')
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderByRaw("(
                            (SELECT COALESCE(SUM(work_price), 0) FROM works w WHERE w.order_id = orders.id AND w.is_deleted = 0)
                            + (SELECT COALESCE(SUM(quantity * price), 0) FROM order_work_materials owm WHERE owm.order_id = orders.id)
                        ) {$direction}");
                    }),

                Tables\Columns\TextColumn::make('client_source')
                    ->label('Источник')
                    ->formatStateUsing(fn(?string $state): string => $state ? (Order::getAvailableClientSources()[$state] ?? $state) : '—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('payment_status')
                    ->label('Оплата')
                    ->getStateUsing(function (Order $record): bool {
                        if ($record->order_payment_type === Order::PAYMENT_TYPE_PAID) {
                            return $record->calculated_price > 0;
                        }
                        return true;
                    })
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(function (Order $record): string {
                        if ($record->order_payment_type === Order::PAYMENT_TYPE_WARRANTY) {
                            return 'Гарантийный заказ';
                        }
                        $isPaid = $record->order_payment_type === Order::PAYMENT_TYPE_PAID && $record->calculated_price > 0;
                        return $isPaid ? 'Оплачено' : 'Не оплачено';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(?string $state): string => $state ? (mb_strlen($state) > 30 ? mb_substr($state, 0, 30) . '...' : $state) : '—'),

                Tables\Columns\TextColumn::make('master.name')
                    ->label('Мастер')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('manager.name')
                    ->label('Менеджер')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('Оборудование')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn($record) => $record && in_array($record->service_type, [Order::TYPE_REPAIR, Order::TYPE_DIAGNOSTIC])),

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
                        return $tools->map(fn($tool) => $tool->tool_type_label . ($tool->quantity > 1 ? " ({$tool->quantity})" : ''))->join(', ');
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn($record) => $record && $record->service_type === Order::TYPE_SHARPENING),

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

                Tables\Filters\SelectFilter::make('client_source')
                    ->label('Откуда пришёл клиент')
                    ->options(Order::getAvailableClientSources()),

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
                Tables\Actions\ViewAction::make()
                    ->iconButton()
                    ->tooltip('Просмотр'),
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip('Редактировать'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('generate_acceptance')
                        ->label('Акт приема')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->visible(fn(Order $record): bool => in_array($record->status, [
                            Order::STATUS_NEW,
                        ]))
                        ->url(fn(Order $record): string => url('/api/orders/' . $record->id . '/documents/view?type=' . DocumentGeneratorFactory::TYPE_ACCEPTANCE))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('generate_issuance')
                        ->label('Акт выдачи')
                        ->icon('heroicon-o-document-arrow-up')
                        ->color('success')
                        ->visible(fn(Order $record): bool => in_array($record->status, [
                            Order::STATUS_READY,
                            Order::STATUS_ISSUED,
                        ]))
                        ->url(fn(Order $record): string => url('/api/orders/' . $record->id . '/documents/view?type=' . DocumentGeneratorFactory::TYPE_ISSUANCE))
                        ->openUrlInNewTab(),
                ])
                    ->iconButton()
                    ->icon('heroicon-o-document-text')
                    ->tooltip('Документы')
                    ->visible(fn(Order $record): bool => in_array($record->status, [
                        Order::STATUS_NEW,
                        Order::STATUS_READY,
                        Order::STATUS_ISSUED,
                    ])),
            ], position: ActionsPosition::BeforeColumns)
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
