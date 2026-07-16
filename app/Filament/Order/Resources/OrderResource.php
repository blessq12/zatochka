<?php

namespace App\Filament\Order\Resources;

use App\Application\Order\Command\AssignOrderMasterCommand;
use App\Application\Order\Command\AssignOrderMasterHandler;
use App\Application\Order\Command\CancelOrderCommand;
use App\Application\Order\Command\CancelOrderHandler;
use App\Application\Order\Command\CloseOrderCommand;
use App\Application\Order\Command\CloseOrderHandler;
use App\Application\Order\Command\IssueOrderCommand;
use App\Application\Order\Command\IssueOrderHandler;
use App\Application\Order\Command\MarkOrderReadyCommand;
use App\Application\Order\Command\MarkOrderReadyHandler;
use App\Application\Order\DTO\OrderContainerItemDTO;
use App\Application\Order\ReadPort\OrderContainerReadPort;
use App\Application\Inventory\Command\WriteOffMaterialCommand;
use App\Application\Inventory\Command\WriteOffMaterialHandler;
use App\Application\Pricing\Command\SetOrderWorkPricesCommand;
use App\Application\Pricing\Command\SetOrderWorkPricesHandler;
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
use App\Infrastructure\Inventory\Model\StockItemModel;
use App\Infrastructure\Inventory\Model\WarehouseMovementModel;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use App\Models\User;
use App\Models\UserRole;
use App\Shared\Domain\DomainException;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
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
            ->with(['client', 'items.equipment.components', 'warrantySourceOrder.client']);
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            OrderStatus::Created->value => 'Создан',
            OrderStatus::MasterAssigned->value => 'Мастер назначен',
            OrderStatus::ReceptionCompleted->value => 'Приёмка завершена',
            OrderStatus::InProgress->value => 'В работе',
            OrderStatus::AwaitingPricing->value => 'Ожидает оценки',
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

    public static function formatOrderItemPrice(OrderItemModel $item): string
    {
        return static::formatOrderItemWorkLineTotal($item);
    }

    /**
     * @return array{unit: float, repairable_quantity: int, currency: string}|null
     */
    private static function resolveOrderItemWorkPricing(OrderItemModel $item): ?array
    {
        if (! static::orderItemHasRepairableQuantity($item)) {
            return null;
        }

        $container = app(OrderContainerReadPort::class)->findById((string) $item->order_id);

        if ($container === null) {
            return null;
        }

        $containerItem = null;

        foreach ($container->items as $candidate) {
            if ($candidate->id === (int) $item->id) {
                $containerItem = $candidate;

                break;
            }
        }

        if ($containerItem === null || $containerItem->estimate === null || ! $containerItem->estimate['calculated']) {
            return null;
        }

        return [
            'unit' => (float) $containerItem->estimate['unit_amount'],
            'repairable_quantity' => static::orderItemRepairableQuantity($item),
            'currency' => (string) $containerItem->estimate['currency'],
        ];
    }

    public static function formatOrderItemWorkUnitPrice(OrderItemModel $item): string
    {
        $pricing = static::resolveOrderItemWorkPricing($item);

        if ($pricing === null) {
            return static::orderItemHasRepairableQuantity($item) ? 'не указана' : '—';
        }

        return static::formatMoney((string) $pricing['unit'], $pricing['currency']);
    }

    public static function formatOrderItemWorkLineTotal(OrderItemModel $item): string
    {
        $pricing = static::resolveOrderItemWorkPricing($item);

        if ($pricing === null) {
            return static::orderItemHasRepairableQuantity($item) ? 'не указана' : '—';
        }

        $lineAmount = $pricing['unit'] * $pricing['repairable_quantity'];

        return static::formatMoney((string) $lineAmount, $pricing['currency']);
    }

    public static function calculateOrderItemsTotal(OrderModel $order): string
    {
        $state = static::resolveOrderItemsTotalState($order);

        if ($state === null) {
            return 'не рассчитана';
        }

        return static::formatMoney((string) $state['total'], $state['currency']);
    }

    public static function formatOrderEstimatedTotal(OrderModel $order): string
    {
        return static::formatMoney(
            (string) $order->estimated_amount,
            (string) ($order->estimated_currency ?: 'RUB'),
        );
    }

    public static function formatOrderActualTotal(OrderModel $order): string
    {
        return static::calculateOrderItemsTotal($order);
    }

    /**
     * @return array{total: float, currency: string}|null
     */
    private static function resolveOrderItemsTotalState(OrderModel $order): ?array
    {
        $container = app(OrderContainerReadPort::class)->findById((string) $order->id);

        if ($container === null) {
            return null;
        }

        $total = 0.0;
        $currency = (string) ($order->estimated_currency ?: 'RUB');
        $hasPricedWorks = false;

        foreach ($container->items as $item) {
            if ($item->status === OrderItemStatus::Rejected->value || $item->repairableQuantity < 1) {
                continue;
            }

            foreach ($item->works as $work) {
                $price = $work['price'] ?? null;

                if ($price === null || ! ($price['calculated'] ?? false)) {
                    continue;
                }

                $hasPricedWorks = true;
                $total += (float) $price['unit_amount'] * $item->repairableQuantity;
                $currency = (string) $price['currency'];
            }
        }

        if (! $hasPricedWorks) {
            return null;
        }

        return [
            'total' => $total,
            'currency' => $currency,
        ];
    }

    public static function formatMoney(string $amount, string $currency = 'RUB'): string
    {
        $symbol = match ($currency) {
            'RUB' => '₽',
            default => $currency,
        };

        return number_format((float) $amount, 2, '.', ' ') . ' ' . $symbol;
    }

    private static function orderItemRepairableQuantity(OrderItemModel $item): int
    {
        if (! static::orderItemHasRepairableQuantity($item)) {
            return 0;
        }

        $quantity = $item->quantity !== null ? (int) $item->quantity : null;

        if ($quantity !== null) {
            return max(0, $quantity - (int) ($item->rejected_quantity ?? 0));
        }

        return 1;
    }

    private static function orderItemHasRepairableQuantity(OrderItemModel $item): bool
    {
        if ((string) $item->status === OrderItemStatus::Rejected->value) {
            return false;
        }

        $quantity = $item->quantity !== null ? (int) $item->quantity : null;
        $rejected = (int) ($item->rejected_quantity ?? 0);

        if ($quantity !== null) {
            return $rejected < $quantity;
        }

        return true;
    }

    public static function orderItemLabel(OrderItemModel $item): string
    {
        if ($item->tool_name !== null && trim((string) $item->tool_name) !== '') {
            return (string) $item->tool_name;
        }

        $equipment = $item->equipment;
        if ($equipment !== null) {
            $label = trim(($equipment->brand ?? '') . ' ' . ($equipment->model_name ?? ''));
            if ($label !== '') {
                return $label;
            }

            if (filled($equipment->title ?? null)) {
                return (string) $equipment->title;
            }
        }

        if ($item->client_equipment_id !== null) {
            return 'Оборудование #' . $item->client_equipment_id;
        }

        return 'Позиция #' . $item->id;
    }

    public static function formatOrderItemRejectionsSummary(OrderModel $order): ?string
    {
        $lines = [];

        foreach ($order->items as $item) {
            $rejectedQuantity = (int) ($item->rejected_quantity ?? 0);
            $reason = trim((string) ($item->rejection_reason ?? ''));

            if ($rejectedQuantity < 1 && (string) $item->status === OrderItemStatus::Rejected->value) {
                $rejectedQuantity = 1;
            }

            if ($rejectedQuantity < 1) {
                continue;
            }

            $label = static::orderItemLabel($item);
            $quantity = $item->quantity !== null ? (int) $item->quantity : null;

            if ($quantity !== null) {
                $label .= sprintf(' — отклонено %d из %d', $rejectedQuantity, $quantity);
            }

            if ($reason !== '') {
                $label .= ': ' . $reason;
            }

            $lines[] = $label;
        }

        return $lines === [] ? null : implode("\n", $lines);
    }

    /**
     * @return list<array{
     *     master_comment_id: int,
     *     order_item_id: int,
     *     position_label: string,
     *     work_description: string,
     *     repairable_quantity: int,
     *     base_amount: ?string,
     * }>
     */
    public static function buildWorkPricesFormDefaults(OrderModel $order): array
    {
        $container = app(OrderContainerReadPort::class)->findById((string) $order->id);

        if ($container === null) {
            return [];
        }

        $rows = [];

        foreach ($container->items as $item) {
            if ($item->status === OrderItemStatus::Rejected->value || $item->repairableQuantity < 1) {
                continue;
            }

            foreach ($item->works as $work) {
                $rows[] = [
                    'master_comment_id' => (int) $work['id'],
                    'order_item_id' => (int) $item->id,
                    'position_label' => static::orderContainerItemLabel($item),
                    'work_description' => (string) $work['description'],
                    'repairable_quantity' => $item->repairableQuantity,
                    'base_amount' => $work['price']['unit_amount'] ?? null,
                ];
            }
        }

        return $rows;
    }

    /**
     * @return list<array{
     *     position: string,
     *     description: string,
     *     repairable_quantity: string,
     *     unit_price: string,
     *     line_total: string,
     * }>
     */
    public static function buildOrderWorkTableRows(OrderModel $order): array
    {
        $container = app(OrderContainerReadPort::class)->findById((string) $order->id);

        if ($container === null) {
            return [];
        }

        $rows = [];

        foreach ($container->items as $item) {
            if ($item->status === OrderItemStatus::Rejected->value || $item->repairableQuantity < 1) {
                continue;
            }

            $position = static::orderContainerItemLabel($item);
            $repairableQuantity = (string) $item->repairableQuantity;

            if ($item->works === []) {
                $rows[] = [
                    'position' => $position,
                    'description' => '—',
                    'repairable_quantity' => $repairableQuantity,
                    'unit_price' => 'не указана',
                    'line_total' => 'не указана',
                ];

                continue;
            }

            foreach ($item->works as $index => $work) {
                $unitPrice = 'не указана';
                $lineTotal = 'не указана';
                $price = $work['price'] ?? null;

                if ($price !== null && ($price['calculated'] ?? false)) {
                    $unitAmount = (float) $price['unit_amount'];
                    $lineAmount = $unitAmount * $item->repairableQuantity;
                    $currency = (string) ($price['currency'] ?? 'RUB');
                    $unitPrice = static::formatMoney((string) $unitAmount, $currency);
                    $lineTotal = static::formatMoney((string) $lineAmount, $currency);
                }

                $rows[] = [
                    'position' => $position,
                    'description' => (string) $work['description'],
                    'repairable_quantity' => $index === 0 ? $repairableQuantity : '—',
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }
        }

        return $rows;
    }

    /**
     * @return list<array{position: string, material: string, quantity: string, comment: string}>
     */
    public static function buildOrderMaterialsTableRows(OrderModel $order): array
    {
        $movements = WarehouseMovementModel::query()
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get();

        $rows = [];

        foreach ($movements as $movement) {
            $position = 'Весь заказ';

            if ($movement->order_item_id !== null) {
                $item = $order->items->firstWhere('id', $movement->order_item_id);
                $position = $item instanceof OrderItemModel
                    ? static::orderItemLabel($item)
                    : 'Позиция #' . $movement->order_item_id;
            }

            $stockItem = StockItemModel::query()
                ->with('material')
                ->find($movement->stock_item_id);

            $materialName = $stockItem?->material?->name
                ?? 'Материал #' . $movement->stock_item_id;

            $rows[] = [
                'position' => $position,
                'material' => $materialName,
                'quantity' => (string) $movement->quantity,
                'comment' => filled($movement->comment) ? (string) $movement->comment : '—',
            ];
        }

        return $rows;
    }

    private static function orderContainerItemLabel(OrderContainerItemDTO $item): string
    {
        if ($item->toolName !== null && trim($item->toolName) !== '') {
            return $item->toolName;
        }

        if ($item->clientEquipmentId !== null) {
            return 'Оборудование #' . $item->clientEquipmentId;
        }

        return 'Позиция #' . $item->id;
    }

    public static function formatMasterInternalComments(OrderModel $order): ?string
    {
        $container = app(OrderContainerReadPort::class)->findById((string) $order->id);

        if ($container === null || $container->masterInternalComments === []) {
            return null;
        }

        return implode("\n\n", array_column($container->masterInternalComments, 'text'));
    }

    public static function clientSelect(string $name = 'client_id'): Select
    {
        return Select::make($name)
            ->label('Клиент')
            ->options(fn(): array => ClientModel::query()
                ->orderBy('name')
                ->orderBy('phone')
                ->get()
                ->mapWithKeys(static function (ClientModel $client): array {
                    $label = trim(($client->name ?: 'Без имени') . ' · ' . $client->phone);

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
                        Grid::make(1)->schema([
                            TextEntry::make('status')
                                ->label('Статус')
                                ->badge()
                                ->size(TextSize::Large)
                                ->formatStateUsing(fn(?string $state): string => static::statusOptions()[$state] ?? ($state ?? '—'))
                                ->color(fn(?string $state): string => match ($state) {
                                    OrderStatus::Cancelled->value => 'danger',
                                    OrderStatus::Issued->value, OrderStatus::Closed->value => 'success',
                                    OrderStatus::Ready->value => 'info',
                                    OrderStatus::AwaitingPricing->value => 'warning',
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
                                    OrderStatus::AwaitingPricing->value => Heroicon::OutlinedBanknotes,
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
                                ->formatStateUsing(fn(?string $state): string => static::urgencyOptions()[$state] ?? ($state ?? '—'))
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
                                ->formatStateUsing(fn(?string $state): string => static::serviceTypeOptions()[$state] ?? ($state ?? '—'))
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
                                ->formatStateUsing(fn(?string $state): string => static::billingTypeOptions()[$state] ?? ($state ?? '—'))
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

                                $number = (string) static::orderNumber($source);
                                $type = static::serviceTypeOptions()[$source->service_type] ?? $source->service_type;

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
                                fn(?string $state, OrderModel $record): string => static::formatOrderEstimatedTotal($record)
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
                                        fn (OrderItemModel $record): string => (string) static::orderItemRepairableQuantity($record)
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
                                    ->formatStateUsing(fn(?string $state): string => static::itemStatusOptions()[$state] ?? ($state ?? '—'))
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
                                    ->formatStateUsing(fn(?string $state): string => static::itemStatusOptions()[$state] ?? ($state ?? '—'))
                                    ->color(fn(?string $state): string => match ($state) {
                                        OrderItemStatus::Rejected->value => 'danger',
                                        OrderItemStatus::Issued->value, OrderItemStatus::Completed->value => 'success',
                                        OrderItemStatus::InProduction->value => 'warning',
                                        default => 'gray',
                                    }),
                            ]),
                        TextEntry::make('id')
                            ->label('Причины отклонения')
                            ->visible(fn(OrderModel $record): bool => static::formatOrderItemRejectionsSummary($record) !== null)
                            ->formatStateUsing(
                                fn(string $state, OrderModel $record): string => static::formatOrderItemRejectionsSummary($record) ?? ''
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
                        TextEntry::make('id')
                            ->label('Комментарий')
                            ->formatStateUsing(
                                fn (string $state, OrderModel $record): string => static::formatMasterInternalComments($record)
                                    ?? 'Мастер не оставил комментарий'
                            )
                            ->placeholder('Мастер не оставил комментарий')
                            ->prose()
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
                                fn(string $state, OrderModel $record): string => static::formatOrderActualTotal($record)
                            )
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->icon(Heroicon::OutlinedCalculator)
                            ->color(fn(string $state, OrderModel $record): string => static::resolveOrderItemsTotalState($record) === null
                                ? 'gray'
                                : 'success')
                            ->columnSpanFull(),
                        RepeatableEntry::make('work_lines')
                            ->label('')
                            ->state(fn(OrderModel $record): array => static::buildOrderWorkTableRows($record))
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
                            ->state(fn(OrderModel $record): array => static::buildOrderMaterialsTableRows($record))
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
                    ->formatStateUsing(fn(?string $state): string => static::serviceTypeOptions()[$state] ?? ($state ?? '—'))
                    ->sortable(),
                TextColumn::make('billing_type')
                    ->label('Вид')
                    ->formatStateUsing(fn(?string $state): string => static::billingTypeOptions()[$state] ?? ($state ?? '—'))
                    ->toggleable(),
                TextColumn::make('urgency')
                    ->label('Срочность')
                    ->formatStateUsing(fn(?string $state): string => static::urgencyOptions()[$state] ?? ($state ?? '—'))
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => static::statusOptions()[$state] ?? ($state ?? '—'))
                    ->color(fn(?string $state): string => match ($state) {
                        OrderStatus::Cancelled->value => 'danger',
                        OrderStatus::Issued->value, OrderStatus::Closed->value => 'success',
                        OrderStatus::Ready->value => 'info',
                        OrderStatus::InProgress->value,
                        OrderStatus::AwaitingPricing->value,
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
        return [
            Action::make('assignMaster')
                ->label('Назначить мастера')
                ->icon(Heroicon::OutlinedUserPlus)
                ->color('primary')
                ->visible(fn(OrderModel $record): bool => $record->status === OrderStatus::Created->value
                    && $record->assigned_master_id === null)
                ->form([
                    Select::make('master_id')
                        ->label('Мастер')
                        ->options(fn(): array => User::query()
                            ->where('role', UserRole::Master->value)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (OrderModel $record, array $data): void {
                    try {
                        app(AssignOrderMasterHandler::class)->handle(new AssignOrderMasterCommand(
                            (string) $record->id,
                            (int) $data['master_id'],
                        ));
                        Notification::make()->title('Мастер назначен')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('setOrderPrices')
                ->label('Оценить работы')
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('warning')
                ->visible(fn(OrderModel $record): bool => $record->status === OrderStatus::AwaitingPricing->value
                    && static::buildWorkPricesFormDefaults($record) !== [])
                ->modalHeading('Стоимость выполненных работ')
                ->modalDescription('Укажите цену за каждую работу по позиции. Итог по работе = цена × количество к выдаче по позиции.')
                ->fillForm(fn(OrderModel $record): array => [
                    'work_prices' => static::buildWorkPricesFormDefaults($record),
                ])
                ->form(fn(OrderModel $record): array => [
                    Repeater::make('work_prices')
                        ->label('')
                        ->schema([
                            Hidden::make('master_comment_id'),
                            Hidden::make('order_item_id'),
                            TextInput::make('position_label')
                                ->label('Позиция')
                                ->disabled()
                                ->dehydrated(false)
                                ->columnSpan(2),
                            TextInput::make('work_description')
                                ->label('Работа')
                                ->disabled()
                                ->dehydrated(false)
                                ->columnSpan(2),
                            TextInput::make('repairable_quantity')
                                ->label('К выдаче')
                                ->disabled()
                                ->dehydrated(false)
                                ->numeric()
                                ->suffix('шт.')
                                ->columnSpan(1),
                            TextInput::make('base_amount')
                                ->label('Цена работы за ед.')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->suffix('₽')
                                ->live(onBlur: true)
                                ->columnSpan(1),
                            Placeholder::make('work_line_total')
                                ->label('Итого по работе')
                                ->content(function (Get $get): string {
                                    $quantity = max(1, (int) $get('repairable_quantity'));
                                    $unitAmount = (float) ($get('base_amount') ?? 0);

                                    if ($unitAmount <= 0) {
                                        return '—';
                                    }

                                    return static::formatMoney((string) round($unitAmount * $quantity, 2));
                                })
                                ->columnSpan(1),
                        ])
                        ->columns(7)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false),
                ])
                ->action(function (OrderModel $record, array $data): void {
                    try {
                        app(SetOrderWorkPricesHandler::class)->handle(new SetOrderWorkPricesCommand(
                            (string) $record->id,
                            array_map(
                                static fn(array $row): array => [
                                    'master_comment_id' => (int) $row['master_comment_id'],
                                    'base_amount' => (string) $row['base_amount'],
                                ],
                                $data['work_prices'] ?? [],
                            ),
                        ));
                        Notification::make()->title('Стоимость работ сохранена')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('writeOffMaterial')
                ->label('Списать материал')
                ->icon(Heroicon::OutlinedArchiveBox)
                ->color('gray')
                ->visible(fn(OrderModel $record): bool => in_array($record->status, [
                    OrderStatus::AwaitingPricing->value,
                    OrderStatus::InProgress->value,
                ], true))
                ->form(fn(OrderModel $record): array => [
                    Select::make('stock_item_id')
                        ->label('Материал')
                        ->options(fn(): array => StockItemModel::query()
                            ->with('material')
                            ->get()
                            ->mapWithKeys(static function (StockItemModel $item): array {
                                $name = $item->material?->name ?? ('#' . $item->id);
                                $qty = $item->quantity_on_hand;

                                return [(int) $item->id => $name . ' (остаток: ' . $qty . ')'];
                            })
                            ->all())
                        ->searchable()
                        ->required(),
                    Select::make('order_item_id')
                        ->label('Позиция (опционально)')
                        ->options(fn(): array => $record->items
                            ->mapWithKeys(static function (OrderItemModel $item): array {
                                $label = $item->tool_name
                                    ?: ($item->equipment?->title ?? ('#' . $item->id));

                                return [(int) $item->id => $label];
                            })
                            ->all())
                        ->searchable(),
                    TextInput::make('quantity')
                        ->label('Количество')
                        ->numeric()
                        ->required()
                        ->minValue(0.001),
                    TextInput::make('comment')
                        ->label('Комментарий'),
                ])
                ->action(function (OrderModel $record, array $data): void {
                    try {
                        app(WriteOffMaterialHandler::class)->handle(new WriteOffMaterialCommand(
                            (int) $data['stock_item_id'],
                            app(SequentialEntityIdGenerator::class)->next('warehouse_movement')->value,
                            (string) $data['quantity'],
                            $data['comment'] ?? null,
                            (string) $record->id,
                            isset($data['order_item_id']) && $data['order_item_id'] !== '' && $data['order_item_id'] !== null
                                ? (int) $data['order_item_id']
                                : null,
                        ));
                        Notification::make()->title('Материал списан')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('markReady')
                ->label('Готов к выдаче')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('success')
                ->visible(fn(OrderModel $record): bool => $record->status === OrderStatus::AwaitingPricing->value)
                ->requiresConfirmation()
                ->action(function (OrderModel $record): void {
                    try {
                        app(MarkOrderReadyHandler::class)->handle(new MarkOrderReadyCommand((string) $record->id));
                        Notification::make()->title('Заказ готов к выдаче')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('issue')
                ->label('Выдать')
                ->icon(Heroicon::OutlinedHandRaised)
                ->visible(fn(OrderModel $record): bool => $record->status === OrderStatus::Ready->value)
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
                ->visible(false)
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
                ->visible(fn(OrderModel $record): bool => ! in_array($record->status, [
                    OrderStatus::Cancelled->value,
                    OrderStatus::Closed->value,
                    OrderStatus::Issued->value,
                    OrderStatus::Ready->value,
                ], true))
                ->requiresConfirmation()
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
