<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ActivityLogRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\OrderWorksRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\OrderMaterialsRelationManager;
use App\Dictionaries\ToolTypeDictionary;
use App\Models\Client;
use App\Models\Order;
use App\Services\Document\Factories\DocumentGeneratorFactory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Заказы';

    protected static ?string $modelLabel = 'Заказ';

    protected static ?string $pluralModelLabel = 'Заказы';

    protected static ?string $navigationGroup = 'Заказы';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'client',
            'master',
            'manager',
            'branch',
            'equipment',
            'tools',
            'parentOrder',
            'orderWorks' => fn ($q) => $q->orderBy('id'),
            'orderMaterials' => fn ($q) => $q->orderBy('id'),
        ]);
    }

    protected static function formatClientOptionLabel(Client $client): string
    {
        $phone = $client->phone ? ' — ' . $client->phone : '';

        return $client->full_name . $phone;
    }

    /**
     * Для листинга: «Фамилия И. О.» (первая часть полностью, далее инициалы по словам).
     */
    protected static function formatClientNameForListing(?string $fullName): string
    {
        if ($fullName === null || trim($fullName) === '') {
            return '—';
        }

        $parts = preg_split('/\s+/u', trim($fullName), -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === []) {
            return '—';
        }

        if (count($parts) === 1) {
            return $parts[0];
        }

        $surname = array_shift($parts);
        $initials = [];
        foreach ($parts as $word) {
            $ch = mb_substr($word, 0, 1);
            if ($ch !== '') {
                $initials[] = mb_strtoupper($ch) . '.';
            }
        }

        return $surname . ($initials !== [] ? ' ' . implode(' ', $initials) : '');
    }

    protected static function clientTablePhoneLine(Order $record): ?string
    {
        $phone = $record->client?->phone;

        return ($phone !== null && $phone !== '') ? $phone : null;
    }

    protected static function tableStatusBadgeColor(string $state): string
    {
        return match ($state) {
            Order::STATUS_NEW => 'gray',
            Order::STATUS_IN_WORK => 'info',
            Order::STATUS_WAITING_PARTS => 'warning',
            Order::STATUS_READY => 'success',
            Order::STATUS_ISSUED => 'primary',
            Order::STATUS_CANCELLED => 'danger',
            default => 'gray',
        };
    }

    public static function orderPaymentSummaryLabel(Order $record): string
    {
        if ($record->order_payment_type === Order::PAYMENT_TYPE_WARRANTY) {
            return 'Гарантийный заказ';
        }
        if ($record->order_payment_type === Order::PAYMENT_TYPE_PAID && $record->calculated_price > 0) {
            return 'Платный: есть начисления';
        }

        return 'Платный: без начислений';
    }

    public static function orderListingSubjectFull(Order $record): string
    {
        if (in_array($record->service_type, [Order::TYPE_REPAIR, Order::TYPE_DIAGNOSTIC], true)) {
            $name = $record->equipment?->name;

            return ($name !== null && $name !== '') ? $name : '—';
        }

        if ($record->service_type === Order::TYPE_SHARPENING) {
            $tools = $record->tools;
            if ($tools->isEmpty()) {
                return '—';
            }

            return $tools->map(fn ($tool) => $tool->tool_type_label . ($tool->quantity > 1 ? " ({$tool->quantity})" : ''))->join(', ');
        }

        return Order::getAvailableTypes()[$record->service_type] ?? '—';
    }

    /**
     * Полный текст предмета работы для модалки (без обрезки: оборудование, СН, описания; по инструментам — тип, кол-во, описание).
     */
    public static function orderModalWorkSubjectHtml(Order $record): HtmlString
    {
        if (in_array($record->service_type, [Order::TYPE_REPAIR, Order::TYPE_DIAGNOSTIC], true)) {
            $e = $record->equipment;
            if ($e === null) {
                return new HtmlString('<p class="text-gray-500 dark:text-gray-400">—</p>');
            }

            $blocks = [];
            $blocks[] = '<div><span class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Полное наименование</span>'
                . '<p class="mt-1 whitespace-pre-wrap break-words">' . e($e->full_name) . '</p></div>';

            if (filled($e->type)) {
                $blocks[] = '<div><span class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Тип оборудования</span>'
                    . '<p class="mt-1 whitespace-pre-wrap break-words">' . e($e->type) . '</p></div>';
            }

            if (filled($e->serial_numbers_display)) {
                $blocks[] = '<div><span class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Серийные номера</span>'
                    . '<p class="mt-1 whitespace-pre-wrap break-words">' . e($e->serial_numbers_display) . '</p></div>';
            }

            if (filled($e->description)) {
                $blocks[] = '<div><span class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Описание</span>'
                    . '<p class="mt-1 whitespace-pre-wrap break-words">' . nl2br(e($e->description)) . '</p></div>';
            }

            return new HtmlString('<div class="space-y-4">' . implode('', $blocks) . '</div>');
        }

        if ($record->service_type === Order::TYPE_SHARPENING) {
            $tools = $record->tools;
            if ($tools->isEmpty()) {
                return new HtmlString('<p class="text-gray-500 dark:text-gray-400">—</p>');
            }

            $items = [];
            foreach ($tools as $tool) {
                $title = e($tool->tool_type_label);
                if ($tool->quantity > 1) {
                    $title .= ' <span class="tabular-nums text-gray-600 dark:text-gray-300">× ' . e((string) $tool->quantity) . '</span>';
                }
                $descBlock = '';
                if (filled($tool->description)) {
                    $descBlock = '<div class="mt-2 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap break-words">'
                        . nl2br(e($tool->description))
                        . '</div>';
                }
                $items[] = '<li class="border-b border-gray-100 py-4 last:border-b-0 dark:border-white/10">' . $title . $descBlock . '</li>';
            }

            return new HtmlString('<ul class="m-0 list-none p-0">' . implode('', $items) . '</ul>');
        }

        $label = Order::getAvailableTypes()[$record->service_type] ?? '—';

        return new HtmlString('<p class="whitespace-pre-wrap break-words">' . e($label) . '</p>');
    }

    protected static function tableServiceTypeBadgeColor(?string $state): string
    {
        return match ($state) {
            Order::TYPE_SHARPENING => 'success',
            Order::TYPE_REPAIR => 'primary',
            Order::TYPE_DIAGNOSTIC => 'warning',
            Order::TYPE_REPLACEMENT => 'info',
            Order::TYPE_MAINTENANCE => 'gray',
            Order::TYPE_CONSULTATION => 'gray',
            Order::TYPE_WARRANTY => 'danger',
            default => 'gray',
        };
    }

    /**
     * @return array<int|string, string>
     */
    protected static function searchClientsForSelect(string $search): array
    {
        $search = trim($search);
        if ($search === '') {
            return [];
        }

        $term = '%' . addcslashes($search, '%_\\') . '%';
        $digits = preg_replace('/\D+/', '', $search) ?? '';

        $query = Client::query()
            ->where(function (Builder $sub) use ($term, $digits) {
                $sub->where('full_name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term);

                if (strlen($digits) >= 3) {
                    if (DB::getDriverName() === 'mysql') {
                        $sub->orWhereRaw(
                            "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(IFNULL(phone,''),' ',''),'-',''),'(',''),')',''),'+','') LIKE ?",
                            ['%' . $digits . '%']
                        );
                    } else {
                        $sub->orWhere('phone', 'like', '%' . addcslashes($digits, '%_\\') . '%');
                    }
                }
            })
            ->orderBy('full_name')
            ->limit(50)
            ->get();

        return $query->mapWithKeys(
            fn (Client $c): array => [$c->id => static::formatClientOptionLabel($c)]
        )->all();
    }

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
                        Forms\Components\Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])
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
                            ]),

                        Forms\Components\Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                Forms\Components\Grid::make([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                    ->schema([
                                        Forms\Components\Select::make('client_id')
                                            ->label('Клиент')
                                            ->relationship('client', 'full_name')
                                            ->searchable()
                                            ->preload(false)
                                            ->required()
                                            ->getSearchResultsUsing(fn(string $search): array => static::searchClientsForSelect($search))
                                            ->getOptionLabelUsing(function ($value): ?string {
                                                if (!$value) {
                                                    return null;
                                                }
                                                $c = Client::find($value);

                                                return $c ? static::formatClientOptionLabel($c) : null;
                                            })
                                            ->createOptionForm($clientCreateForm),

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

                                        Forms\Components\Select::make('branch_id')
                                            ->label('Филиал')
                                            ->relationship('branch', 'name')
                                            ->searchable()->preload()->required()
                                            ->default(fn() => \App\Models\Branch::first()?->id),

                                        Forms\Components\Select::make('urgency')
                                            ->label('Срочность')
                                            ->options(Order::getAvailableUrgencies())
                                            ->default(Order::URGENCY_NORMAL),
                                    ]),

                                Forms\Components\Grid::make([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                    ->schema([
                                        Forms\Components\Select::make('parent_order_id')
                                            ->label('Исходный заказ (для возврата)')
                                            ->relationship('parentOrder', 'order_number')
                                            ->searchable()
                                            ->preload()
                                            ->nullable(),

                                        Forms\Components\Select::make('status')
                                            ->label('Статус')
                                            ->options(Order::getAvailableStatuses())
                                            ->required()->default(Order::STATUS_NEW),

                                        Forms\Components\Select::make('service_type')
                                            ->label('Тип услуги')
                                            ->options([
                                                Order::TYPE_SHARPENING => 'Заточка',
                                                Order::TYPE_REPAIR => 'Ремонт',
                                                Order::TYPE_DIAGNOSTIC => 'Диагностика',
                                            ])
                                            ->required()->default(Order::TYPE_REPAIR)->live(),

                                        Forms\Components\Select::make('order_payment_type')
                                            ->label('Вид')
                                            ->options([
                                                Order::PAYMENT_TYPE_PAID => 'Платный',
                                                Order::PAYMENT_TYPE_WARRANTY => 'Гарантийный',
                                            ])
                                            ->default(Order::PAYMENT_TYPE_PAID)->required()->live(),
                                    ]),
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
                    ->copyable()
                    ->grow(false),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Тип')
                    ->formatStateUsing(fn (?string $state): string => $state ? (Order::getAvailableTypes()[$state] ?? $state) : '—')
                    ->badge()
                    ->color(fn (?string $state): string => static::tableServiceTypeBadgeColor($state))
                    ->searchable()
                    ->sortable()
                    ->grow(false),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn (?string $state): string => $state ? (Order::getAvailableStatuses()[$state] ?? $state) : '—')
                    ->badge()
                    ->color(fn (?string $state): string => $state ? static::tableStatusBadgeColor($state) : 'gray')
                    ->searchable()
                    ->sortable()
                    ->grow(false),

                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->wrap()
                    ->formatStateUsing(fn (?string $state): string => static::formatClientNameForListing($state))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $term = '%' . addcslashes($search, '%_\\') . '%';

                        return $query->whereHas('client', function (Builder $q) use ($term) {
                            $q->where('full_name', 'like', $term)
                                ->orWhere('phone', 'like', $term)
                                ->orWhere('email', 'like', $term);
                        });
                    })
                    ->sortable()
                    ->description(fn (Order $record): ?string => static::clientTablePhoneLine($record)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.y H:i')
                    ->sortable()
                    ->grow(false),
            ])
            ->recordClasses(fn (Order $record): ?string => $record->urgency === Order::URGENCY_URGENT
                ? 'border-s-4 border-danger-600 dark:border-danger-500'
                : null)
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
                Tables\Actions\Action::make('orderOverview')
                    ->label('')
                    ->icon('heroicon-o-rectangle-stack')
                    ->iconButton()
                    ->color('gray')
                    ->tooltip('Сводка заказа')
                    ->modalHeading(fn (Order $record): string => 'Заказ ' . ($record->order_number ?? '#' . $record->getKey()))
                    ->modalWidth(MaxWidth::FourExtraLarge)
                    ->modalContent(fn (Order $record) => view('filament.tables.modals.order-overview', ['record' => $record]))
                    ->modalContentFooter(function (Order $record): HtmlString {
                        $viewUrl = static::getUrl('view', ['record' => $record]);
                        $editUrl = static::getUrl('edit', ['record' => $record]);

                        return new HtmlString(
                            '<div class="flex flex-wrap gap-x-4 gap-y-2 text-sm">' .
                            '<a href="' . e($viewUrl) . '" class="font-medium text-primary-600 hover:underline dark:text-primary-400">' .
                            'Страница заказа</a>' .
                            '<a href="' . e($editUrl) . '" class="font-medium text-primary-600 hover:underline dark:text-primary-400">' .
                            'Редактировать</a>' .
                            '</div>'
                        );
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Закрыть')
                    ->action(static function (): void {}),
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
