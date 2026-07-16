<?php

namespace App\Filament\Order\Resources;

use App\Application\Order\Command\CancelOrderCommand;
use App\Application\Order\Command\CancelOrderHandler;
use App\Application\Order\Command\CloseOrderCommand;
use App\Application\Order\Command\CloseOrderHandler;
use App\Application\Order\Command\IssueOrderCommand;
use App\Application\Order\Command\IssueOrderHandler;
use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderItemStatus;
use App\Domain\Order\VO\OrderNumber;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Order\VO\OrderUrgency;
use App\Domain\Order\VO\SharpeningToolType;
use App\Filament\CRM\Resources\ClientResource;
use App\Filament\Order\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Order\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Order\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Support\DomainResource;
use App\Infrastructure\CRM\Model\ClientModel;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Shared\Domain\DomainException;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
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

        return (string) static::orderNumber($record);
    }

    public static function orderNumber(OrderModel $record): OrderNumber
    {
        return new OrderNumber((string) $record->number);
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
            ->with(['client', 'items.equipment', 'warrantySourceOrder.client']);
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            OrderStatus::Created->value => 'Создан',
            OrderStatus::ReceptionCompleted->value => 'Приёмка завершена',
            OrderStatus::InProgress->value => 'В работе',
            OrderStatus::Ready->value => 'Готов',
            OrderStatus::Cancelled->value => 'Отменён',
            OrderStatus::Closed->value => 'Закрыт',
            OrderStatus::Issued->value => 'Выдан',
        ];
    }

    /** @return array<string, string> */
    public static function serviceTypeOptions(): array
    {
        return [
            OrderServiceType::Sharpening->value => 'Заточка',
            OrderServiceType::Repair->value => 'Ремонт',
        ];
    }

    /** @return array<string, string> */
    public static function billingTypeOptions(): array
    {
        return [
            OrderBillingType::Paid->value => 'Платный',
            OrderBillingType::Warranty->value => 'Гарантийный',
        ];
    }

    /** @return array<string, string> */
    public static function urgencyOptions(): array
    {
        return [
            OrderUrgency::Normal->value => 'Обычный',
            OrderUrgency::Urgent->value => 'Срочный',
        ];
    }

    /** @return array<string, string> */
    public static function itemStatusOptions(): array
    {
        return [
            OrderItemStatus::Accepted->value => 'Принят',
            OrderItemStatus::InProduction->value => 'В производстве',
            OrderItemStatus::Completed->value => 'Готов',
            OrderItemStatus::Rejected->value => 'Отклонён',
            OrderItemStatus::Issued->value => 'Выдан',
        ];
    }

    public static function clientSelect(string $name = 'client_id'): Select
    {
        return Select::make($name)
            ->label('Клиент')
            ->options(fn (): array => ClientModel::query()
                ->orderBy('name')
                ->orderBy('phone')
                ->get()
                ->mapWithKeys(static function (ClientModel $client): array {
                    $label = trim(($client->name ?: 'Без имени').' · '.$client->phone);

                    return [(int) $client->id => $label];
                })
                ->all())
            ->searchable()
            ->required();
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
                    Grid::make(2)->schema([
                        TextEntry::make('status')
                            ->label('Статус')
                            ->badge()
                            ->size(TextSize::Large)
                            ->formatStateUsing(fn (?string $state): string => static::statusOptions()[$state] ?? ($state ?? '—'))
                            ->color(fn (?string $state): string => match ($state) {
                                OrderStatus::Cancelled->value => 'danger',
                                OrderStatus::Issued->value, OrderStatus::Closed->value => 'success',
                                OrderStatus::Ready->value => 'info',
                                OrderStatus::InProgress->value, OrderStatus::ReceptionCompleted->value => 'warning',
                                default => 'gray',
                            })
                            ->icon(fn (?string $state): Heroicon => match ($state) {
                                OrderStatus::Cancelled->value => Heroicon::OutlinedXCircle,
                                OrderStatus::Issued->value => Heroicon::OutlinedHandRaised,
                                OrderStatus::Closed->value => Heroicon::OutlinedCheckCircle,
                                OrderStatus::Ready->value => Heroicon::OutlinedCheckBadge,
                                OrderStatus::InProgress->value => Heroicon::OutlinedCog6Tooth,
                                OrderStatus::ReceptionCompleted->value => Heroicon::OutlinedInboxArrowDown,
                                default => Heroicon::OutlinedClock,
                            }),
                        TextEntry::make('estimated_amount')
                            ->label('Ориентировочная стоимость')
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->formatStateUsing(fn (?string $state): string => $state !== null
                                ? number_format((float) $state, 2, '.', ' ').' ₽'
                                : '—')
                            ->icon(Heroicon::OutlinedBanknotes),
                    ]),
                    Grid::make(4)->schema([
                        TextEntry::make('urgency')
                            ->label('Срочность')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => static::urgencyOptions()[$state] ?? ($state ?? '—'))
                            ->color(fn (?string $state): string => match ($state) {
                                OrderUrgency::Urgent->value => 'danger',
                                default => 'gray',
                            })
                            ->icon(fn (?string $state): Heroicon => match ($state) {
                                OrderUrgency::Urgent->value => Heroicon::OutlinedBolt,
                                default => Heroicon::OutlinedClock,
                            }),
                        TextEntry::make('service_type')
                            ->label('Тип')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => static::serviceTypeOptions()[$state] ?? ($state ?? '—'))
                            ->color(fn (?string $state): string => match ($state) {
                                OrderServiceType::Sharpening->value => 'info',
                                OrderServiceType::Repair->value => 'warning',
                                default => 'gray',
                            })
                            ->icon(fn (?string $state): Heroicon => match ($state) {
                                OrderServiceType::Sharpening->value => Heroicon::OutlinedScissors,
                                OrderServiceType::Repair->value => Heroicon::OutlinedWrenchScrewdriver,
                                default => Heroicon::OutlinedCube,
                            }),
                        TextEntry::make('billing_type')
                            ->label('Вид')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => static::billingTypeOptions()[$state] ?? ($state ?? '—'))
                            ->color(fn (?string $state): string => match ($state) {
                                OrderBillingType::Paid->value => 'success',
                                OrderBillingType::Warranty->value => 'danger',
                                default => 'gray',
                            })
                            ->icon(fn (?string $state): Heroicon => match ($state) {
                                OrderBillingType::Paid->value => Heroicon::OutlinedBanknotes,
                                OrderBillingType::Warranty->value => Heroicon::OutlinedShieldCheck,
                                default => Heroicon::OutlinedTag,
                            }),
                        TextEntry::make('created_at')
                            ->label('Создан')
                            ->dateTime('d.m.Y H:i')
                            ->icon(Heroicon::OutlinedCalendarDays),
                    ]),
                    TextEntry::make('warranty_source_order_id')
                        ->label('Гарантия по заказу')
                        ->icon(Heroicon::OutlinedShieldCheck)
                        ->formatStateUsing(function (?int $state, OrderModel $record): string {
                            if ($state === null) {
                                return '—';
                            }

                            $source = $record->warrantySourceOrder;
                            if ($source === null) {
                                return 'ORD-??-'.$state;
                            }

                            $number = (string) static::orderNumber($source);
                            $type = static::serviceTypeOptions()[$source->service_type] ?? $source->service_type;

                            return $number.' · '.$type;
                        })
                        ->url(fn (?int $state): ?string => $state !== null
                            ? static::getUrl('view', ['record' => $state])
                            : null)
                        ->color('primary')
                        ->visible(fn (OrderModel $record): bool => $record->billing_type === OrderBillingType::Warranty->value),
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
                            ->url(fn (OrderModel $record): ?string => $record->client_id
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
            Section::make(fn (OrderModel $record): string => 'Состав · '.$record->items->count())
                ->description(fn (OrderModel $record): string => $record->service_type === OrderServiceType::Sharpening->value
                    ? 'Инструменты на заточку'
                    : 'Оборудование на ремонт')
                ->icon(Heroicon::OutlinedCube)
                ->columnSpanFull()
                ->schema([
                    RepeatableEntry::make('items')
                        ->hiddenLabel()
                        ->visible(fn (OrderModel $record): bool => $record->service_type === OrderServiceType::Sharpening->value)
                        ->table([
                            TableColumn::make('Наименование'),
                            TableColumn::make('Тип инструмента'),
                            TableColumn::make('Кол-во'),
                            TableColumn::make('Статус'),
                        ])
                        ->schema([
                            TextEntry::make('tool_name')
                                ->placeholder('—'),
                            TextEntry::make('tool_type')
                                ->formatStateUsing(fn (?string $state): string => filled($state)
                                    ? (SharpeningToolType::tryFrom($state)?->label() ?? $state)
                                    : '—'),
                            TextEntry::make('quantity')
                                ->placeholder('—')
                                ->alignCenter(),
                            TextEntry::make('status')
                                ->badge()
                                ->formatStateUsing(fn (?string $state): string => static::itemStatusOptions()[$state] ?? ($state ?? '—'))
                                ->color(fn (?string $state): string => match ($state) {
                                    OrderItemStatus::Rejected->value => 'danger',
                                    OrderItemStatus::Issued->value, OrderItemStatus::Completed->value => 'success',
                                    OrderItemStatus::InProduction->value => 'warning',
                                    default => 'gray',
                                }),
                        ]),
                    RepeatableEntry::make('items')
                        ->hiddenLabel()
                        ->visible(fn (OrderModel $record): bool => $record->service_type === OrderServiceType::Repair->value)
                        ->table([
                            TableColumn::make('Оборудование'),
                            TableColumn::make('Бренд / модель'),
                            TableColumn::make('Статус'),
                        ])
                        ->schema([
                            TextEntry::make('tool_name')
                                ->formatStateUsing(function (?string $state, OrderItemModel $record): string {
                                    if (filled($state)) {
                                        return $state;
                                    }

                                    $equipment = $record->equipment;

                                    if ($equipment === null) {
                                        return 'Оборудование #'.($record->client_equipment_id ?? '—');
                                    }

                                    return (string) $equipment->title;
                                }),
                            TextEntry::make('tool_type')
                                ->formatStateUsing(function (?string $state, OrderItemModel $record): string {
                                    $equipment = $record->equipment;

                                    if ($equipment === null) {
                                        return '—';
                                    }

                                    return trim($equipment->brand.' '.$equipment->model_name) ?: '—';
                                }),
                            TextEntry::make('status')
                                ->badge()
                                ->formatStateUsing(fn (?string $state): string => static::itemStatusOptions()[$state] ?? ($state ?? '—'))
                                ->color(fn (?string $state): string => match ($state) {
                                    OrderItemStatus::Rejected->value => 'danger',
                                    OrderItemStatus::Issued->value, OrderItemStatus::Completed->value => 'success',
                                    OrderItemStatus::InProduction->value => 'warning',
                                    default => 'gray',
                                }),
                        ]),
                ]),
            Section::make('Приёмка')
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('delivery_required')
                        ->label('Доставка')
                        ->formatStateUsing(fn (?bool $state): string => $state ? 'Нужна' : 'Нет')
                        ->badge()
                        ->color(fn (?bool $state): string => $state ? 'success' : 'gray')
                        ->icon(fn (?bool $state): Heroicon => $state
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
                            return $state !== null ? '#'.$state : '—';
                        }

                        return trim(($client->name ?: 'Без имени').' · '.$client->phone);
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
                    ->formatStateUsing(fn (?string $state): string => static::serviceTypeOptions()[$state] ?? ($state ?? '—'))
                    ->sortable(),
                TextColumn::make('billing_type')
                    ->label('Вид')
                    ->formatStateUsing(fn (?string $state): string => static::billingTypeOptions()[$state] ?? ($state ?? '—'))
                    ->toggleable(),
                TextColumn::make('urgency')
                    ->label('Срочность')
                    ->formatStateUsing(fn (?string $state): string => static::urgencyOptions()[$state] ?? ($state ?? '—'))
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::statusOptions()[$state] ?? ($state ?? '—'))
                    ->color(fn (?string $state): string => match ($state) {
                        OrderStatus::Cancelled->value => 'danger',
                        OrderStatus::Issued->value, OrderStatus::Closed->value => 'success',
                        OrderStatus::Ready->value => 'info',
                        OrderStatus::InProgress->value, OrderStatus::ReceptionCompleted->value => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('estimated_amount')
                    ->label('Сумма')
                    ->formatStateUsing(fn (?string $state): string => $state !== null ? $state.' ₽' : '—')
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
        return [
            Action::make('issue')
                ->label('Выдать')
                ->icon(Heroicon::OutlinedHandRaised)
                ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::Ready->value)
                ->action(function (OrderModel $record): void {
                    try {
                        app(IssueOrderHandler::class)->handle(new IssueOrderCommand((string) $record->id));
                        Notification::make()->title('Заказ выдан')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('close')
                ->label('Закрыть')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->visible(fn (OrderModel $record): bool => $record->status === OrderStatus::Ready->value)
                ->action(function (OrderModel $record): void {
                    try {
                        app(CloseOrderHandler::class)->handle(new CloseOrderCommand((string) $record->id));
                        Notification::make()->title('Заказ закрыт')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('cancel')
                ->label('Отменить')
                ->icon(Heroicon::OutlinedXMark)
                ->color('danger')
                ->visible(fn (OrderModel $record): bool => ! in_array($record->status, [
                    OrderStatus::Cancelled->value,
                    OrderStatus::Closed->value,
                    OrderStatus::Issued->value,
                ], true))
                ->form([
                    TextInput::make('reason')
                        ->label('Причина')
                        ->required(),
                ])
                ->action(function (OrderModel $record, array $data): void {
                    try {
                        app(CancelOrderHandler::class)->handle(new CancelOrderCommand(
                            (string) $record->id,
                            $data['reason'],
                        ));
                        Notification::make()->title('Заказ отменён')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
        ];
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
