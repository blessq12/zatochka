<?php

namespace App\Filament\Feedback\Resources\ReviewResource\Support;

use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderItemStatus;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Order\VO\OrderUrgency;
use App\Domain\Order\VO\SharpeningToolType;
use App\Filament\CRM\Resources\ClientResource;
use App\Filament\Order\Resources\OrderResource;
use App\Filament\Order\Resources\OrderResource\Support\OrderPresentation;
use App\Filament\Order\Resources\OrderResource\Support\OrderWorkPricing;
use App\Infrastructure\Feedback\Model\ReviewModel;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

final class ReviewInfolist
{
    /** @return list<Component> */
    public static function components(): array
    {
        return [
            self::reviewSection(),
            self::clientSection(),
            self::orderSection(),
        ];
    }

    private static function reviewSection(): Section
    {
        return Section::make('Отзыв')
            ->icon(Heroicon::OutlinedChatBubbleBottomCenterText)
            ->columnSpanFull()
            ->schema([
                Grid::make(2)->schema([
                    TextEntry::make('status')
                        ->label('Статус')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => ReviewPresentation::statusLabel($state))
                        ->color(fn (?string $state): string => ReviewPresentation::statusColor($state)),
                    TextEntry::make('rating')
                        ->label('Оценка')
                        ->html()
                        ->formatStateUsing(fn (?int $state): Htmlable => ReviewPresentation::ratingStarsHtml((int) ($state ?? 0))),
                    TextEntry::make('submitted_at')
                        ->label('Отправлен')
                        ->dateTime('d.m.Y H:i'),
                    TextEntry::make('moderated_at')
                        ->label('Модерация')
                        ->dateTime('d.m.Y H:i')
                        ->placeholder('—'),
                ]),
                TextEntry::make('comment')
                    ->label('Текст отзыва')
                    ->placeholder('Без комментария')
                    ->prose()
                    ->columnSpanFull(),
                TextEntry::make('manager_reply')
                    ->label('Ответ менеджера')
                    ->placeholder('Нет ответа')
                    ->prose()
                    ->columnSpanFull(),
            ]);
    }

    private static function clientSection(): Section
    {
        return Section::make('Клиент')
            ->icon(Heroicon::OutlinedUserCircle)
            ->columnSpanFull()
            ->schema([
                TextEntry::make('client_compact')
                    ->label('Клиент')
                    ->state(function (ReviewModel $record): string {
                        $name = ReviewPresentation::clientName($record);
                        $phone = ReviewPresentation::clientPhone($record);

                        return $name.' · '.$phone;
                    })
                    ->url(fn (ReviewModel $record): ?string => $record->client_id
                        ? ClientResource::getUrl('view', ['record' => $record->client_id])
                        : null)
                    ->color('primary')
                    ->weight(FontWeight::SemiBold)
                    ->icon(Heroicon::OutlinedUserCircle),
            ]);
    }

    private static function orderSection(): Section
    {
        return Section::make('Заказ')
            ->icon(Heroicon::OutlinedClipboardDocumentList)
            ->columnSpanFull()
            ->schema([
                Grid::make(2)->schema([
                    TextEntry::make('order_number')
                        ->label('Номер')
                        ->state(fn (ReviewModel $record): string => ReviewPresentation::orderNumberLabel($record))
                        ->url(fn (ReviewModel $record): ?string => filled($record->order_id)
                            ? OrderResource::getUrl('view', ['record' => $record->order_id])
                            : null)
                        ->color('primary')
                        ->weight(FontWeight::SemiBold),
                    TextEntry::make('order.status')
                        ->label('Статус')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => OrderStatus::tryLabel($state) ?? ($state ?? '—'))
                        ->color(fn (?string $state): string => OrderStatus::tryColor($state)),
                    TextEntry::make('order.service_type')
                        ->label('Тип')
                        ->formatStateUsing(fn (?string $state): string => OrderServiceType::tryLabel($state) ?? ($state ?? '—')),
                    TextEntry::make('order.billing_type')
                        ->label('Вид')
                        ->formatStateUsing(fn (?string $state): string => OrderBillingType::tryLabel($state) ?? ($state ?? '—')),
                    TextEntry::make('order.urgency')
                        ->label('Срочность')
                        ->formatStateUsing(fn (?string $state): string => OrderUrgency::tryLabel($state) ?? ($state ?? '—')),
                    TextEntry::make('order.estimated_amount')
                        ->label('Ориентировочная стоимость')
                        ->formatStateUsing(fn (?string $state, ReviewModel $record): string => $state !== null
                            ? $state.' '.((string) ($record->order?->estimated_currency ?: 'RUB'))
                            : '—'),
                ]),
                self::orderCompositionEntry(),
                self::orderWorksEntry(),
            ]);
    }

    private static function orderCompositionEntry(): RepeatableEntry
    {
        return RepeatableEntry::make('order.items')
            ->label('Состав заказа')
            ->columnSpanFull()
            ->visible(fn (ReviewModel $record): bool => $record->order !== null)
            ->table([
                TableColumn::make('Позиция'),
                TableColumn::make('Детали'),
                TableColumn::make('Кол-во'),
                TableColumn::make('Статус'),
            ])
            ->schema([
                TextEntry::make('position')
                    ->state(fn (OrderItemModel $item): string => OrderPresentation::orderItemLabel($item)),
                TextEntry::make('details')
                    ->state(function (OrderItemModel $item): string {
                        if ($item->tool_type !== null) {
                            return SharpeningToolType::tryLabel((string) $item->tool_type) ?? (string) $item->tool_type;
                        }

                        $equipment = $item->equipment;
                        if ($equipment === null) {
                            return '—';
                        }

                        $brandModel = trim(($equipment->brand ?? '').' '.($equipment->model_name ?? ''));

                        return $brandModel !== '' ? $brandModel : ((string) ($equipment->title ?? '—'));
                    }),
                TextEntry::make('quantity')
                    ->state(function (OrderItemModel $item): string {
                        if ($item->quantity !== null) {
                            return (string) $item->quantity;
                        }

                        return '1';
                    })
                    ->alignCenter(),
                TextEntry::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => OrderItemStatus::tryLabel($state) ?? ($state ?? '—'))
                    ->color(fn (?string $state): string => match ($state) {
                        OrderItemStatus::Rejected->value => 'danger',
                        OrderItemStatus::Issued->value, OrderItemStatus::Completed->value => 'success',
                        OrderItemStatus::InProduction->value => 'warning',
                        default => 'gray',
                    }),
            ])
            ->placeholder('В заказе нет позиций');
    }

    private static function orderWorksEntry(): RepeatableEntry
    {
        return RepeatableEntry::make('order_works')
            ->label('Работы по заказу')
            ->columnSpanFull()
            ->visible(fn (ReviewModel $record): bool => $record->order !== null)
            ->state(function (ReviewModel $record): array {
                $order = $record->order;

                if (! $order instanceof OrderModel) {
                    return [];
                }

                return OrderWorkPricing::buildOrderWorkTableRows($order);
            })
            ->table([
                TableColumn::make('Позиция'),
                TableColumn::make('Работа'),
                TableColumn::make('К выдаче'),
                TableColumn::make('Цена за ед.'),
                TableColumn::make('Итого'),
            ])
            ->schema([
                self::workStateEntry('position'),
                self::workStateEntry('work_description', 'description'),
                self::workStateEntry('repairable_quantity')->alignCenter(),
                self::workStateEntry('unit_price')->alignEnd(),
                self::workStateEntry('line_total')->alignEnd(),
            ])
            ->placeholder('Выполненные работы ещё не зафиксированы');
    }

    private static function workStateEntry(string $name, ?string $key = null): TextEntry
    {
        $key ??= $name;

        return TextEntry::make($name)
            ->state(function (TextEntry $component) use ($key): string {
                $item = $component->getContainer()->getConstantState();

                return is_array($item) ? (string) ($item[$key] ?? '—') : '—';
            });
    }
}
