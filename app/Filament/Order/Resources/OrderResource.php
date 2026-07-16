<?php

namespace App\Filament\Order\Resources;

use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderItemStatus;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Order\VO\OrderUrgency;
use App\Domain\Order\VO\SharpeningToolType;
use App\Filament\CRM\Resources\ClientResource;
use App\Filament\Order\Resources\OrderResource\Actions\OrderMutationActions;
use App\Filament\Order\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Order\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Order\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Order\Resources\OrderResource\Support\OrderPresentation;
use App\Filament\Support\DomainResource;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class OrderResource extends DomainResource
{
    protected static ?string $model = OrderModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Заказы';

    protected static ?string $navigationLabel = 'Заказы';

    protected static ?string $modelLabel = 'Заказ';

    protected static ?string $pluralModelLabel = 'Заказы';

    protected static ?int $navigationSort = 10;

    public static function hasRecordTitle(): bool
    {
        return true;
    }

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        if (! $record instanceof OrderModel) {
            return static::getModelLabel();
        }

        return (string) OrderPresentation::orderNumber($record);
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canView(Model $record): bool
    {
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['client', 'items.equipment.components', 'warrantySourceOrder.client']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Сводка')
                    ->icon(Heroicon::OutlinedClipboardDocumentList)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(1)->schema([
                            TextEntry::make('status')
                                ->label('Статус')
                                ->badge()
                                ->size(TextSize::Large)
                                ->formatStateUsing(fn(?string $state): string => OrderPresentation::statusOptions()[$state] ?? ($state ?? '—'))
                                ->color(fn(?string $state): string => match ($state) {
                                    OrderStatus::Cancelled->value => 'danger',
                                    OrderStatus::Issued->value, OrderStatus::Closed->value => 'success',
                                    OrderStatus::Ready->value => 'info',
                                    OrderStatus::WorksCompleted->value => 'warning',
                                    OrderStatus::InProgress->value,
                                    OrderStatus::MasterAssigned->value,
                                    OrderStatus::ReceptionCompleted->value => 'warning',
                                    default => 'gray',
                                })
                                ->icon(fn(?string $state): Heroicon => match ($state) {
                                    OrderStatus::Cancelled->value => Heroicon::OutlinedXCircle,
                                    OrderStatus::Issued->value => Heroicon::OutlinedHandRaised,
                                    OrderStatus::Closed->value => Heroicon::OutlinedCheckCircle,
                                    OrderStatus::Ready->value => Heroicon::OutlinedCheckBadge,
                                    OrderStatus::WorksCompleted->value => Heroicon::OutlinedBanknotes,
                                    OrderStatus::InProgress->value => Heroicon::OutlinedCog6Tooth,
                                    OrderStatus::MasterAssigned->value => Heroicon::OutlinedUserPlus,
                                    OrderStatus::ReceptionCompleted->value => Heroicon::OutlinedInboxArrowDown,
                                    default => Heroicon::OutlinedClock,
                                }),
                        ]),
                        Grid::make(4)->schema([
                            TextEntry::make('urgency')
                                ->label('Срочность')
                                ->badge()
                                ->formatStateUsing(fn(?string $state): string => OrderPresentation::urgencyOptions()[$state] ?? ($state ?? '—'))
                                ->color(fn(?string $state): string => match ($state) {
                                    OrderUrgency::Urgent->value => 'danger',
                                    default => 'gray',
                                })
                                ->icon(fn(?string $state): Heroicon => match ($state) {
                                    OrderUrgency::Urgent->value => Heroicon::OutlinedBolt,
                                    default => Heroicon::OutlinedClock,
                                }),
                            TextEntry::make('service_type')
                                ->label('Тип')
                                ->badge()
                                ->formatStateUsing(fn(?string $state): string => OrderPresentation::serviceTypeOptions()[$state] ?? ($state ?? '—'))
                                ->color(fn(?string $state): string => match ($state) {
                                    OrderServiceType::Sharpening->value => 'info',
                                    OrderServiceType::Repair->value => 'warning',
                                    default => 'gray',
                                })
                                ->icon(fn(?string $state): Heroicon => match ($state) {
                                    OrderServiceType::Sharpening->value => Heroicon::OutlinedScissors,
                                    OrderServiceType::Repair->value => Heroicon::OutlinedWrenchScrewdriver,
                                    default => Heroicon::OutlinedCube,
                                }),
                            TextEntry::make('billing_type')
                                ->label('Вид')
                                ->badge()
                                ->formatStateUsing(fn(?string $state): string => OrderPresentation::billingTypeOptions()[$state] ?? ($state ?? '—'))
                                ->color(fn(?string $state): string => match ($state) {
                                    OrderBillingType::Paid->value => 'success',
                                    OrderBillingType::Warranty->value => 'danger',
                                    default => 'gray',
                                })
                                ->icon(fn(?string $state): Heroicon => match ($state) {
                                    OrderBillingType::Paid->value => Heroicon::OutlinedBanknotes,
                                    OrderBillingType::Warranty->value => Heroicon::OutlinedShieldCheck,
                                    default => Heroicon::OutlinedTag,
                                }),
                            TextEntry::make('created_at')
                                ->label('Создан')
                                ->dateTime('d.m.Y H:i')
                                ->icon(Heroicon::OutlinedCalendarDays),
                        ]),
                        TextEntry::make('assigned_master_id')
                            ->label('Мастер')
                            ->icon(Heroicon::OutlinedUser)
                            ->formatStateUsing(function (?int $state): string {
                                if ($state === null) {
                                    return 'Не назначен';
                                }

                                $master = User::query()->find($state);

                                return $master?->name ?? ('#' . $state);
                            }),
                        TextEntry::make('warranty_source_order_id')
                            ->label('Гарантия по заказу')
                            ->icon(Heroicon::OutlinedShieldCheck)
                            ->formatStateUsing(function (?int $state, OrderModel $record): string {
                                if ($state === null) {
                                    return '—';
                                }

                                $source = $record->warrantySourceOrder;
                                if ($source === null) {
                                    return 'ORD-??-' . $state;
                                }

                                $number = (string) OrderPresentation::orderNumber($source);
                                $type = OrderPresentation::serviceTypeOptions()[$source->service_type] ?? $source->service_type;

                                return $number . ' · ' . $type;
                            })
                            ->url(fn(?int $state): ?string => $state !== null
                                ? static::getUrl('view', ['record' => $state])
                                : null)
                            ->color('primary')
                            ->visible(fn(OrderModel $record): bool => $record->billing_type === OrderBillingType::Warranty->value),
                    ]),
                Section::make('Клиент')
                    ->icon(Heroicon::OutlinedUser)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('client.name')
                                ->label('ФИО')
                                ->weight(FontWeight::SemiBold)
                                ->placeholder('Без имени')
                                ->url(fn(OrderModel $record): ?string => $record->client_id
                                    ? ClientResource::getUrl('view', ['record' => $record->client_id])
                                    : null)
                                ->color('primary')
                                ->icon(Heroicon::OutlinedUserCircle),
                            TextEntry::make('client.phone')
                                ->label('Телефон')
                                ->copyable()
                                ->copyMessage('Телефон скопирован')
                                ->icon(Heroicon::OutlinedPhone),
                            TextEntry::make('client.email')
                                ->label('Эл. почта')
                                ->placeholder('—')
                                ->icon(Heroicon::OutlinedEnvelope)
                                ->copyable()
                                ->copyMessage('Почта скопирована'),
                        ]),
                    ]),
                Section::make(fn(OrderModel $record): string => 'Состав · ' . $record->items->count())
                    ->description('Позиции заказа')
                    ->icon(Heroicon::OutlinedCube)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('estimated_amount')
                            ->label('Ориентировочная стоимость')
                            ->helperText('Указана при создании заказа')
                            ->formatStateUsing(
                                fn(?string $state, OrderModel $record): string => OrderPresentation::formatOrderEstimatedTotal($record)
                            )
                            ->weight(FontWeight::SemiBold)
                            ->size(TextSize::Large)
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->color('gray')
                            ->columnSpanFull(),
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->visible(fn(OrderModel $record): bool => $record->service_type === OrderServiceType::Sharpening->value)
                            ->table([
                                TableColumn::make('Наименование'),
                                TableColumn::make('Тип инструмента'),
                                TableColumn::make('Кол-во'),
                                TableColumn::make('К выдаче'),
                                TableColumn::make('Отклонено'),
                                TableColumn::make('Статус'),
                            ])
                            ->schema([
                                TextEntry::make('tool_name')
                                    ->placeholder('—'),
                                TextEntry::make('tool_type')
                                    ->formatStateUsing(fn(?string $state): string => filled($state)
                                        ? (SharpeningToolType::tryFrom($state)?->label() ?? $state)
                                        : '—'),
                                TextEntry::make('quantity')
                                    ->placeholder('—')
                                    ->alignCenter(),
                                TextEntry::make('repairable_quantity')
                                    ->state(
                                        fn (OrderItemModel $record): string => (string) OrderPresentation::orderItemRepairableQuantity($record)
                                    )
                                    ->alignCenter(),
                                TextEntry::make('rejected_quantity')
                                    ->formatStateUsing(fn(?int $state, OrderItemModel $record): string => sprintf(
                                        '%d из %d',
                                        (int) ($state ?? 0),
                                        (int) ($record->quantity ?? 0),
                                    ))
                                    ->alignCenter(),
                                TextEntry::make('status')
                                    ->badge()
                                    ->formatStateUsing(fn(?string $state): string => OrderPresentation::itemStatusOptions()[$state] ?? ($state ?? '—'))
                                    ->color(fn(?string $state): string => match ($state) {
                                        OrderItemStatus::Rejected->value => 'danger',
                                        OrderItemStatus::Issued->value, OrderItemStatus::Completed->value => 'success',
                                        OrderItemStatus::InProduction->value => 'warning',
                                        default => 'gray',
                                    }),
                            ]),
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->visible(fn(OrderModel $record): bool => $record->service_type === OrderServiceType::Repair->value)
                            ->table([
                                TableColumn::make('Оборудование'),
                                TableColumn::make('Бренд / модель'),
                                TableColumn::make('Части'),
                                TableColumn::make('Статус'),
                            ])
                            ->schema([
                                TextEntry::make('equipment.title')
                                    ->placeholder(fn(OrderItemModel $record): string => $record->client_equipment_id
                                        ? 'Оборудование #' . $record->client_equipment_id
                                        : '—'),
                                TextEntry::make('equipment.brand')
                                    ->formatStateUsing(function (?string $state, OrderItemModel $record): string {
                                        $equipment = $record->equipment;

                                        if ($equipment === null) {
                                            return '—';
                                        }

                                        return trim($equipment->brand . ' ' . $equipment->model_name) ?: '—';
                                    }),
                                TextEntry::make('equipment.components')
                                    ->formatStateUsing(function (mixed $state, OrderItemModel $record): string {
                                        $components = $record->equipment?->components;

                                        if ($components === null || $components->isEmpty()) {
                                            return '—';
                                        }

                                        return $components
                                            ->map(static function ($component): string {
                                                $label = (string) $component->name;
                                                if (filled($component->serial_number ?? null)) {
                                                    $label .= ' (' . $component->serial_number . ')';
                                                }

                                                return $label;
                                            })
                                            ->implode(', ');
                                    }),
                                TextEntry::make('status')
                                    ->badge()
                                    ->formatStateUsing(fn(?string $state): string => OrderPresentation::itemStatusOptions()[$state] ?? ($state ?? '—'))
                                    ->color(fn(?string $state): string => match ($state) {
                                        OrderItemStatus::Rejected->value => 'danger',
                                        OrderItemStatus::Issued->value, OrderItemStatus::Completed->value => 'success',
                                        OrderItemStatus::InProduction->value => 'warning',
                                        default => 'gray',
                                    }),
                            ]),
                        TextEntry::make('id')
                            ->label('Причины отклонения')
                            ->visible(fn(OrderModel $record): bool => OrderPresentation::formatOrderItemRejectionsSummary($record) !== null)
                            ->formatStateUsing(
                                fn(string $state, OrderModel $record): string => OrderPresentation::formatOrderItemRejectionsSummary($record) ?? ''
                            )
                            ->icon(Heroicon::OutlinedExclamationTriangle)
                            ->color('danger')
                            ->prose()
                            ->columnSpanFull(),
                    ]),
                Section::make('Комментарий мастера')
                    ->description('Внутренний комментарий по задаче мастерской — только для сотрудников')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('master_internal_comments')
                            ->label('Комментарий')
                            ->state(
                                fn (OrderModel $record): string => OrderPresentation::formatMasterInternalComments($record)
                                    ?? 'Мастер не оставил комментарий'
                            )
                            ->placeholder('Мастер не оставил комментарий')
                            ->prose()
                            ->columnSpanFull(),
                        TextEntry::make('manager_rework_comment')
                            ->label('Возврат на доработку')
                            ->visible(fn (OrderModel $record): bool => filled($record->manager_rework_comment))
                            ->prose()
                            ->color('warning')
                            ->icon(Heroicon::OutlinedArrowUturnLeft)
                            ->columnSpanFull(),
                    ]),
                Section::make('Работы')
                    ->description('Выполненные работы по позициям и оценка каждой работы: цена × количество к выдаче')
                    ->icon(Heroicon::OutlinedWrenchScrewdriver)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('id')
                            ->label('Итого по работам')
                            ->helperText('Сумма стоимости работ по всем позициям')
                            ->formatStateUsing(
                                fn(string $state, OrderModel $record): string => OrderPresentation::formatOrderActualTotal($record)
                            )
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->icon(Heroicon::OutlinedCalculator)
                            ->color(fn(string $state, OrderModel $record): string => OrderPresentation::resolveOrderItemsTotalState($record) === null
                                ? 'gray'
                                : 'success')
                            ->columnSpanFull(),
                        RepeatableEntry::make('work_lines')
                            ->label('')
                            ->state(fn(OrderModel $record): array => OrderPresentation::buildOrderWorkTableRows($record))
                            ->table([
                                TableColumn::make('Позиция'),
                                TableColumn::make('Выполненная работа'),
                                TableColumn::make('К выдаче'),
                                TableColumn::make('Стоимость работы за ед.'),
                                TableColumn::make('Итого за работы'),
                            ])
                            ->schema([
                                TextEntry::make('position')
                                    ->state(function (TextEntry $component): string {
                                        $item = $component->getContainer()->getConstantState();

                                        return is_array($item) ? (string) ($item['position'] ?? '—') : '—';
                                    }),
                                TextEntry::make('work_description')
                                    ->state(function (TextEntry $component): string {
                                        $item = $component->getContainer()->getConstantState();

                                        return is_array($item) ? (string) ($item['description'] ?? '—') : '—';
                                    }),
                                TextEntry::make('repairable_quantity')
                                    ->state(function (TextEntry $component): string {
                                        $item = $component->getContainer()->getConstantState();

                                        return is_array($item) ? (string) ($item['repairable_quantity'] ?? '—') : '—';
                                    })
                                    ->alignCenter(),
                                TextEntry::make('unit_price')
                                    ->state(function (TextEntry $component): string {
                                        $item = $component->getContainer()->getConstantState();

                                        return is_array($item) ? (string) ($item['unit_price'] ?? '—') : '—';
                                    })
                                    ->alignEnd(),
                                TextEntry::make('line_total')
                                    ->state(function (TextEntry $component): string {
                                        $item = $component->getContainer()->getConstantState();

                                        return is_array($item) ? (string) ($item['line_total'] ?? '—') : '—';
                                    })
                                    ->alignEnd(),
                            ])
                            ->placeholder('Выполненные работы ещё не зафиксированы'),
                    ]),
                Section::make('Материалы')
                    ->description('Списанные материалы по заказу')
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('material_lines')
                            ->label('')
                            ->state(fn(OrderModel $record): array => OrderPresentation::buildOrderMaterialsTableRows($record))
                            ->table([
                                TableColumn::make('Позиция'),
                                TableColumn::make('Материал'),
                                TableColumn::make('Кол-во'),
                                TableColumn::make('Комментарий'),
                            ])
                            ->schema([
                                TextEntry::make('position'),
                                TextEntry::make('material'),
                                TextEntry::make('quantity')
                                    ->alignCenter(),
                                TextEntry::make('comment')
                                    ->placeholder('—'),
                            ])
                            ->placeholder('Материалы по заказу не списывались'),
                    ]),
                Section::make('Приёмка')
                    ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('delivery_required')
                            ->label('Доставка')
                            ->formatStateUsing(fn(?bool $state): string => $state ? 'Нужна' : 'Нет')
                            ->badge()
                            ->color(fn(?bool $state): string => $state ? 'success' : 'gray')
                            ->icon(fn(?bool $state): Heroicon => $state
                                ? Heroicon::OutlinedTruck
                                : Heroicon::OutlinedXMark),
                        TextEntry::make('defects')
                            ->label('Дефекты')
                            ->placeholder('Не указаны')
                            ->prose()
                            ->columnSpanFull()
                            ->icon(Heroicon::OutlinedExclamationTriangle),
                        TextEntry::make('internal_notes')
                            ->label('Внутренние заметки')
                            ->placeholder('Нет')
                            ->prose()
                            ->columnSpanFull()
                            ->icon(Heroicon::OutlinedLockClosed),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Номер')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client_id')
                    ->label('Клиент')
                    ->formatStateUsing(function (?int $state, OrderModel $record): string {
                        $client = $record->client;

                        if ($client === null) {
                            return $state !== null ? '#' . $state : '—';
                        }

                        return trim(($client->name ?: 'Без имени') . ' · ' . $client->phone);
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('client', function (Builder $client) use ($search): void {
                            $client->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                TextColumn::make('service_type')
                    ->label('Тип')
                    ->formatStateUsing(fn(?string $state): string => OrderPresentation::serviceTypeOptions()[$state] ?? ($state ?? '—'))
                    ->sortable(),
                TextColumn::make('billing_type')
                    ->label('Вид')
                    ->formatStateUsing(fn(?string $state): string => OrderPresentation::billingTypeOptions()[$state] ?? ($state ?? '—'))
                    ->toggleable(),
                TextColumn::make('urgency')
                    ->label('Срочность')
                    ->formatStateUsing(fn(?string $state): string => OrderPresentation::urgencyOptions()[$state] ?? ($state ?? '—'))
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => OrderPresentation::statusOptions()[$state] ?? ($state ?? '—'))
                    ->color(fn(?string $state): string => match ($state) {
                        OrderStatus::Cancelled->value => 'danger',
                        OrderStatus::Issued->value, OrderStatus::Closed->value => 'success',
                        OrderStatus::Ready->value => 'info',
                        OrderStatus::InProgress->value,
                        OrderStatus::WorksCompleted->value,
                        OrderStatus::MasterAssigned->value,
                        OrderStatus::ReceptionCompleted->value => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('estimated_amount')
                    ->label('Сумма')
                    ->formatStateUsing(fn(?string $state): string => $state !== null ? $state . ' ₽' : '—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()->label('Просмотр'),
                ...static::orderMutationActions(),
            ]);
    }

    /** @return list<Action> */
    public static function orderMutationActions(): array
    {
        return OrderMutationActions::all();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }
}
