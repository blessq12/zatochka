<?php

namespace App\Filament\Order\Resources\OrderResource\Support;

use App\Application\Delivery\DTO\DeliveryRequestDTO;
use App\Application\Delivery\ReadPort\DeliveryReadPort;
use App\Application\Finance\DTO\PaymentDTO;
use App\Application\Finance\ReadPort\PaymentReadPort;
use App\Application\Order\ReadPort\OrderContainerReadPort;
use App\Domain\Delivery\VO\DeliveryStatus;
use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderItemStatus;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderSource;
use App\Domain\Order\VO\OrderUrgency;
use App\Domain\Order\VO\SharpeningToolType;
use App\Domain\Workshop\VO\ProductionStatus;
use App\Filament\CRM\Resources\ClientResource;
use App\Filament\Delivery\Resources\DeliveryRequestResource;
use App\Filament\Finance\Pages\CashDeskDashboard;
use App\Filament\Finance\Support\PaymentPresentation;
use App\Filament\Order\Resources\OrderResource;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Models\User;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

/**
 * Инфолист страницы просмотра заказа: контейнер read-срезов BC.
 */
final class OrderInfolist
{
    /** @return list<Component> */
    public static function components(): array
    {
        return [
            self::summarySection(),
            self::parametersSection(),
            self::compositionSection(),
            self::workshopSection(),
            self::inventorySection(),
            self::financeSection(),
            self::deliverySection(),
            self::metaSection(),
        ];
    }

    private static function summarySection(): Section
    {
        return Section::make('Сводка')
            ->icon(Heroicon::OutlinedClipboardDocumentList)
            ->columnSpanFull()
            ->schema([
                TextEntry::make('client_compact')
                    ->label('Клиент')
                    ->state(function (OrderModel $record): string {
                        $name = filled($record->client?->name) ? (string) $record->client->name : 'Без имени';
                        $phone = filled($record->client?->phone) ? (string) $record->client->phone : '—';

                        return $name.' · '.$phone;
                    })
                    ->url(fn (OrderModel $record): ?string => $record->client_id
                        ? ClientResource::getUrl('view', ['record' => $record->client_id])
                        : null)
                    ->color('primary')
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->weight(FontWeight::SemiBold),
                TextEntry::make('assigned_master_id')
                    ->label('Мастер')
                    ->icon(Heroicon::OutlinedUser)
                    ->formatStateUsing(function (?int $state): string {
                        if ($state === null) {
                            return 'Не назначен';
                        }

                        $master = User::query()->find($state);

                        return $master?->name ?? ('#'.$state);
                    }),
                TextEntry::make('source')
                    ->label('Источник')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => OrderSource::tryLabel($state) ?? ($state ?? '—'))
                    ->color(fn (?string $state): string => match ($state) {
                        OrderSource::Website->value => 'info',
                        OrderSource::Api->value => 'gray',
                        default => 'primary',
                    })
                    ->icon(Heroicon::OutlinedGlobeAlt),
                TextEntry::make('master_internal_comments')
                    ->label('Комментарий мастера')
                    ->state(
                        fn (OrderModel $record): string => OrderPresentation::formatMasterInternalComments($record)
                            ?? 'Мастер не оставил комментарий'
                    )
                    ->prose()
                    ->columnSpanFull()
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight),
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

                        $number = (string) OrderPresentation::orderNumber($source);
                        $type = OrderServiceType::tryLabel($source->service_type) ?? $source->service_type;

                        return $number.' · '.$type;
                    })
                    ->url(fn (?int $state): ?string => $state !== null
                        ? OrderResource::getUrl('view', ['record' => $state])
                        : null)
                    ->color('primary')
                    ->visible(fn (OrderModel $record): bool => $record->billing_type === OrderBillingType::Warranty->value),
                TextEntry::make('client_comment')
                    ->label('Комментарий клиента')
                    ->placeholder('Нет')
                    ->prose()
                    ->columnSpanFull()
                    ->icon(Heroicon::OutlinedChatBubbleBottomCenterText),
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
            ]);
    }

    private static function parametersSection(): Section
    {
        return Section::make('Параметры')
            ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
            ->columnSpanFull()
            ->schema([
                Grid::make(2)->schema([
                    Html::make(fn (OrderModel $record): Htmlable => self::parameterBadgesHtml($record)),
                    TextEntry::make('estimated_amount')
                        ->label('Ориентировочная стоимость')
                        ->formatStateUsing(
                            fn (?string $state, OrderModel $record): string => OrderWorkPricing::formatOrderEstimatedTotal($record)
                        )
                        ->weight(FontWeight::SemiBold)
                        ->icon(Heroicon::OutlinedCalculator),
                ]),
                Grid::make(2)->schema([
                    TextEntry::make('pricing_final')
                        ->label('Финальная цена')
                        ->state(fn (OrderModel $record): string => self::pricingLine($record, 'final'))
                        ->weight(FontWeight::Bold)
                        ->size(TextSize::Large)
                        ->color(fn (OrderModel $record): string => self::pricingLineColor($record, 'final'))
                        ->icon(Heroicon::OutlinedBanknotes),
                    Grid::make(1)->schema([
                        TextEntry::make('pricing_works')
                            ->label('Стоимость работ')
                            ->state(fn (OrderModel $record): string => self::pricingLine($record, 'works'))
                            ->color(fn (OrderModel $record): string => self::pricingLineColor($record, 'works'))
                            ->icon(Heroicon::OutlinedWrenchScrewdriver),
                        TextEntry::make('pricing_parts')
                            ->label('Стоимость запчастей')
                            ->state(fn (OrderModel $record): string => self::pricingLine($record, 'parts'))
                            ->color(fn (OrderModel $record): string => self::pricingLineColor($record, 'parts'))
                            ->icon(Heroicon::OutlinedCube),
                    ]),
                ]),
            ]);
    }

    /** @param  'final'|'works'|'parts'  $key */
    private static function pricingLine(OrderModel $record, string $key): string
    {
        return OrderWorkPricing::formatPricingState(
            OrderWorkPricing::resolveOrderPricingBreakdown($record)[$key],
        );
    }

    /** @param  'final'|'works'|'parts'  $key */
    private static function pricingLineColor(OrderModel $record, string $key): string
    {
        $state = OrderWorkPricing::resolveOrderPricingBreakdown($record)[$key];

        if ($state === null) {
            return 'gray';
        }

        return $key === 'final' ? 'success' : 'gray';
    }

    private static function parameterBadgesHtml(OrderModel $record): Htmlable
    {
        $serviceColor = match ($record->service_type) {
            OrderServiceType::Sharpening->value => 'info',
            OrderServiceType::Repair->value => 'warning',
            default => 'gray',
        };
        $billingColor = match ($record->billing_type) {
            OrderBillingType::Paid->value => 'success',
            OrderBillingType::Warranty->value => 'danger',
            default => 'gray',
        };
        $urgencyColor = $record->urgency === OrderUrgency::Urgent->value ? 'danger' : 'gray';

        return new HtmlString(Blade::render(
            <<<'BLADE'
            <div class="flex flex-wrap items-center gap-2">
                <x-filament::badge :color="$serviceColor" size="lg">{{ $service }}</x-filament::badge>
                <x-filament::badge :color="$billingColor" size="lg">{{ $billing }}</x-filament::badge>
                <x-filament::badge :color="$urgencyColor" size="lg">{{ $urgency }}</x-filament::badge>
            </div>
            BLADE,
            [
                'service' => OrderServiceType::tryLabel($record->service_type) ?? '—',
                'serviceColor' => $serviceColor,
                'billing' => OrderBillingType::tryLabel($record->billing_type) ?? '—',
                'billingColor' => $billingColor,
                'urgency' => OrderUrgency::tryLabel($record->urgency) ?? '—',
                'urgencyColor' => $urgencyColor,
            ],
        ));
    }

    private static function metaSection(): Section
    {
        return Section::make('Служебное')
            ->description('Технические даты записи')
            ->icon(Heroicon::OutlinedInformationCircle)
            ->collapsed()
            ->columnSpanFull()
            ->schema([
                Grid::make(2)->schema([
                    TextEntry::make('created_at')
                        ->label('Создан')
                        ->dateTime('d.m.Y H:i')
                        ->icon(Heroicon::OutlinedCalendarDays),
                    TextEntry::make('updated_at')
                        ->label('Обновлён')
                        ->dateTime('d.m.Y H:i')
                        ->placeholder('—')
                        ->icon(Heroicon::OutlinedArrowPath),
                ]),
            ]);
    }

    private static function compositionSection(): Section
    {
        return Section::make(fn(OrderModel $record): string => 'Состав · ' . $record->items->count())
            ->icon(Heroicon::OutlinedCube)
            ->columnSpanFull()
            ->schema([
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
                            ->formatStateUsing(fn(?string $state): string => SharpeningToolType::tryLabel($state) ?? '—'),
                        TextEntry::make('quantity')
                            ->placeholder('—')
                            ->alignCenter(),
                        TextEntry::make('repairable_quantity')
                            ->state(
                                fn(OrderItemModel $record): string => (string) OrderPresentation::orderItemRepairableQuantity($record)
                            )
                            ->alignCenter(),
                        TextEntry::make('rejected_quantity')
                            ->formatStateUsing(fn(?int $state, OrderItemModel $record): string => sprintf(
                                '%d из %d',
                                (int) ($state ?? 0),
                                (int) ($record->quantity ?? 0),
                            ))
                            ->alignCenter(),
                        self::itemStatusEntry(),
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
                        self::itemStatusEntry(),
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
            ]);
    }

    private static function itemStatusEntry(): TextEntry
    {
        return TextEntry::make('status')
            ->badge()
            ->formatStateUsing(fn(?string $state): string => OrderItemStatus::tryLabel($state) ?? ($state ?? '—'))
            ->color(fn(?string $state): string => match ($state) {
                OrderItemStatus::Rejected->value => 'danger',
                OrderItemStatus::Issued->value, OrderItemStatus::Completed->value => 'success',
                OrderItemStatus::InProduction->value => 'warning',
                default => 'gray',
            });
    }

    private static function workshopSection(): Section
    {
        return Section::make('Мастерская')
            ->icon(Heroicon::OutlinedWrenchScrewdriver)
            ->columnSpanFull()
            ->schema([
                TextEntry::make('production_task_status')
                    ->label('Задача')
                    ->state(function (OrderModel $record): string {
                        $task = app(OrderContainerReadPort::class)->findById((string) $record->id)?->productionTask;

                        if ($task === null) {
                            return 'Задача мастерской ещё не создана';
                        }

                        $status = ProductionStatus::tryFrom((string) $task['status']);
                        $label = $status !== null
                            ? self::productionStatusLabel($status)
                            : (string) $task['status'];

                        return '#' . $task['id'] . ' · ' . $label;
                    })
                    ->icon(Heroicon::OutlinedClipboardDocumentList)
                    ->columnSpanFull(),
                TextEntry::make('manager_rework_comment')
                    ->label('Возврат на доработку')
                    ->visible(fn(OrderModel $record): bool => filled($record->manager_rework_comment))
                    ->prose()
                    ->color('warning')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->columnSpanFull(),
                TextEntry::make('id')
                    ->label('Итого по работам')
                    ->helperText('Сумма стоимости работ по всем позициям')
                    ->formatStateUsing(
                        fn(string $state, OrderModel $record): string => OrderWorkPricing::formatOrderActualTotal($record)
                    )
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Large)
                    ->icon(Heroicon::OutlinedCalculator)
                    ->color(fn(string $state, OrderModel $record): string => OrderWorkPricing::resolveOrderItemsTotalState($record) === null
                        ? 'gray'
                        : 'success')
                    ->columnSpanFull(),
                RepeatableEntry::make('work_lines')
                    ->label('Работы')
                    ->hiddenLabel()
                    ->state(fn(OrderModel $record): array => OrderWorkPricing::buildOrderWorkTableRows($record))
                    ->table([
                        TableColumn::make('Позиция'),
                        TableColumn::make('Выполненная работа'),
                        TableColumn::make('К выдаче'),
                        TableColumn::make('Стоимость работы за ед.'),
                        TableColumn::make('Итого за работы'),
                    ])
                    ->schema([
                        self::constantStateEntry('position'),
                        self::constantStateEntry('work_description', 'description'),
                        self::constantStateEntry('repairable_quantity')->alignCenter(),
                        self::constantStateEntry('unit_price')->alignEnd(),
                        self::constantStateEntry('line_total')->alignEnd(),
                    ])
                    ->placeholder('Выполненные работы ещё не зафиксированы'),
            ]);
    }

    private static function productionStatusLabel(ProductionStatus $status): string
    {
        return match ($status) {
            ProductionStatus::Queued => 'В очереди',
            ProductionStatus::MasterAssigned => 'Мастер назначен',
            ProductionStatus::Diagnosed => 'Диагностика',
            ProductionStatus::Rejected => 'Отклонено',
            ProductionStatus::InWork => 'В работе',
            ProductionStatus::WaitingParts => 'Ожидание запчастей',
            ProductionStatus::WorkCompleted => 'Работы выполнены',
            ProductionStatus::Completed => 'Завершено',
        };
    }

    private static function constantStateEntry(string $name, ?string $key = null): TextEntry
    {
        $key ??= $name;

        return TextEntry::make($name)
            ->state(function (TextEntry $component) use ($key): string {
                $item = $component->getContainer()->getConstantState();

                return is_array($item) ? (string) ($item[$key] ?? '—') : '—';
            });
    }

    private static function inventorySection(): Section
    {
        return Section::make('Склад')
            ->icon(Heroicon::OutlinedArchiveBox)
            ->columnSpanFull()
            ->schema([
                RepeatableEntry::make('material_lines')
                    ->label('Материалы')
                    ->hiddenLabel()
                    ->state(fn(OrderModel $record): array => OrderPresentation::buildOrderMaterialsTableRows($record))
                    ->table([
                        TableColumn::make('Позиция'),
                        TableColumn::make('Материал'),
                        TableColumn::make('Кол-во'),
                        TableColumn::make('Цена за ед.'),
                        TableColumn::make('Сумма'),
                        TableColumn::make('Комментарий'),
                    ])
                    ->schema([
                        TextEntry::make('position'),
                        TextEntry::make('material'),
                        TextEntry::make('quantity')
                            ->alignCenter(),
                        TextEntry::make('unit_price')
                            ->alignEnd(),
                        TextEntry::make('line_total')
                            ->alignEnd(),
                        TextEntry::make('comment')
                            ->placeholder('—'),
                    ])
                    ->placeholder('Материалы не списывались'),
            ]);
    }

    private static function financeSection(): Section
    {
        return Section::make('Оплата')
            ->icon(Heroicon::OutlinedBanknotes)
            ->columnSpanFull()
            ->schema([
                TextEntry::make('payment_number')
                    ->label('Платёж')
                    ->state(function (OrderModel $record): string {
                        $payment = self::paymentFor($record);

                        return $payment === null ? 'Платежа нет' : $payment->number;
                    })
                    ->icon(Heroicon::OutlinedReceiptPercent)
                    ->weight(FontWeight::SemiBold),
                TextEntry::make('payment_method')
                    ->label('Метод')
                    ->state(function (OrderModel $record): string {
                        $payment = self::paymentFor($record);

                        return $payment === null
                            ? '—'
                            : PaymentPresentation::methodLabel($payment->method);
                    })
                    ->visible(fn(OrderModel $record): bool => self::paymentFor($record) !== null),
                TextEntry::make('payment_amount')
                    ->label('Сумма')
                    ->state(function (OrderModel $record): string {
                        $payment = self::paymentFor($record);

                        return $payment === null
                            ? '—'
                            : PaymentPresentation::formatMoney($payment->amount, $payment->currency);
                    })
                    ->visible(fn(OrderModel $record): bool => self::paymentFor($record) !== null)
                    ->weight(FontWeight::Bold)
                    ->color('success'),
                TextEntry::make('cash_desk_link')
                    ->label('Касса')
                    ->state('Открыть в Кассе')
                    ->url(fn(): string => CashDeskDashboard::getUrl())
                    ->color('primary')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->visible(fn(OrderModel $record): bool => self::paymentFor($record) !== null),
            ]);
    }

    private static function deliverySection(): Section
    {
        return Section::make('Доставка')
            ->icon(Heroicon::OutlinedTruck)
            ->columnSpanFull()
            ->schema([
                TextEntry::make('delivery_summary')
                    ->label('Статус')
                    ->state(function (OrderModel $record): string {
                        if (! $record->delivery_required) {
                            return 'Доставка не требуется';
                        }

                        $delivery = self::deliveryFor($record);

                        if ($delivery === null) {
                            return 'Нужна · заявка ещё не создана';
                        }

                        return self::deliveryStatusLabel($delivery->status);
                    })
                    ->badge()
                    ->color(function (OrderModel $record): string {
                        if (! $record->delivery_required) {
                            return 'gray';
                        }

                        $delivery = self::deliveryFor($record);

                        if ($delivery === null) {
                            return 'warning';
                        }

                        return match ($delivery->status) {
                            DeliveryStatus::Delivered->value => 'success',
                            DeliveryStatus::Cancelled->value => 'danger',
                            default => 'info',
                        };
                    })
                    ->icon(fn(OrderModel $record): Heroicon => $record->delivery_required
                        ? Heroicon::OutlinedTruck
                        : Heroicon::OutlinedXMark),
                TextEntry::make('delivery_address')
                    ->label('Адрес')
                    ->state(function (OrderModel $record): string {
                        $delivery = self::deliveryFor($record);

                        return $delivery === null ? '—' : self::formatDeliveryAddress($delivery);
                    })
                    ->visible(fn(OrderModel $record): bool => self::deliveryFor($record) !== null)
                    ->icon(Heroicon::OutlinedMapPin)
                    ->columnSpanFull(),
                TextEntry::make('delivery_mode')
                    ->label('Режим')
                    ->state(function (OrderModel $record): string {
                        $delivery = self::deliveryFor($record);

                        return $delivery?->pickup ? 'Самовывоз / забор' : 'Доставка клиенту';
                    })
                    ->visible(fn(OrderModel $record): bool => self::deliveryFor($record) !== null),
                TextEntry::make('delivery_link')
                    ->label('Заявка')
                    ->state('Открыть в Доставке')
                    ->url(function (OrderModel $record): ?string {
                        $delivery = self::deliveryFor($record);

                        return $delivery === null
                            ? null
                            : DeliveryRequestResource::getUrl('view', ['record' => $delivery->id]);
                    })
                    ->color('primary')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->visible(fn(OrderModel $record): bool => self::deliveryFor($record) !== null),
            ]);
    }

    private static function paymentFor(OrderModel $record): ?PaymentDTO
    {
        return app(PaymentReadPort::class)->findByOrderId((string) $record->id);
    }

    private static function deliveryFor(OrderModel $record): ?DeliveryRequestDTO
    {
        return app(DeliveryReadPort::class)->findByOrderId((string) $record->id);
    }

    private static function deliveryStatusLabel(string $status): string
    {
        return match ($status) {
            DeliveryStatus::Requested->value => 'Заявка создана',
            DeliveryStatus::CourierAssigned->value => 'Курьер назначен',
            DeliveryStatus::Collected->value => 'Забрано',
            DeliveryStatus::Delivered->value => 'Доставлено',
            DeliveryStatus::Cancelled->value => 'Отменено',
            default => $status,
        };
    }

    private static function formatDeliveryAddress(DeliveryRequestDTO $delivery): string
    {
        $parts = array_filter([
            $delivery->city,
            $delivery->street,
            $delivery->building,
            $delivery->apartment !== null && $delivery->apartment !== ''
                ? 'кв. ' . $delivery->apartment
                : null,
        ], static fn(?string $part): bool => filled($part));

        return $parts === [] ? '—' : implode(', ', $parts);
    }
}
