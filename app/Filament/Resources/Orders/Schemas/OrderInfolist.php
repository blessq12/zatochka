<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Filament\Resources\Orders\Actions\OrderManageActions;
use App\Filament\Support\OrderViewPresenter;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Callout::make(fn (OrderModel $record): ?string => OrderViewPresenter::statusHint($record))
                    ->color(fn (OrderModel $record): string => OrderViewPresenter::statusColor($record->status))
                    ->icon(fn (OrderModel $record) => OrderViewPresenter::statusIcon($record->status))
                    ->visible(fn (OrderModel $record): bool => OrderViewPresenter::statusHint($record) !== null),

                Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        Section::make('Заказ')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->columnSpan(['default' => 1, 'lg' => 2])
                            ->columns(2)
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Статус')
                                    ->badge()
                                    ->color(fn (OrderModel $record): string => OrderViewPresenter::statusColor($record->status))
                                    ->icon(fn (OrderModel $record) => OrderViewPresenter::statusIcon($record->status))
                                    ->formatStateUsing(fn (OrderModel $record): string => $record->status->label()),
                                TextEntry::make('master_id')
                                    ->label('Мастер')
                                    ->icon('heroicon-o-user')
                                    ->placeholder('Не назначен')
                                    ->color(fn (OrderModel $record): ?string => $record->master_id === null ? 'warning' : null)
                                    ->formatStateUsing(fn (OrderModel $record): string => OrderViewPresenter::masterName($record->master_id) ?? 'Не назначен'),
                                TextEntry::make('service_types')
                                    ->label('Услуги')
                                    ->badge()
                                    ->formatStateUsing(function (mixed $state): string {
                                        if (is_string($state)) {
                                            return OrderViewPresenter::serviceTypeLabel($state);
                                        }

                                        return implode(', ', OrderViewPresenter::serviceTypeLabels($state));
                                    }),
                                TextEntry::make('urgency')
                                    ->label('Срочность')
                                    ->badge()
                                    ->color(fn (OrderModel $record): string => OrderViewPresenter::urgencyColor($record->urgency))
                                    ->formatStateUsing(fn (OrderModel $record): string => OrderViewPresenter::urgencyLabel($record->urgency)),
                                TextEntry::make('client_snapshot.full_name')
                                    ->label('Клиент')
                                    ->icon('heroicon-o-user-circle')
                                    ->weight(FontWeight::Medium)
                                    ->placeholder('—')
                                    ->formatStateUsing(fn (mixed $state, OrderModel $record): string => is_string($state)
                                        ? $state
                                        : OrderViewPresenter::clientDisplayName($record)),
                                TextEntry::make('client_snapshot.phone')
                                    ->label('Телефон')
                                    ->icon('heroicon-o-phone')
                                    ->copyable()
                                    ->placeholder('—')
                                    ->formatStateUsing(fn (mixed $state, OrderModel $record): string => is_string($state)
                                        ? $state
                                        : (OrderViewPresenter::clientPhone($record) ?? '—')),
                                TextEntry::make('needs_delivery')
                                    ->label('Доставка')
                                    ->badge()
                                    ->formatStateUsing(fn (OrderModel $record): string => $record->needs_delivery ? 'Нужна доставка' : 'Самовывоз')
                                    ->color(fn (OrderModel $record): string => $record->needs_delivery ? 'info' : 'gray'),
                                TextEntry::make('delivery_address')
                                    ->label('Адрес доставки')
                                    ->placeholder('—')
                                    ->visible(fn (OrderModel $record): bool => $record->needs_delivery)
                                    ->columnSpanFull(),
                                TextEntry::make('equipment_id')
                                    ->label('Оборудование')
                                    ->icon('heroicon-o-cpu-chip')
                                    ->placeholder('Не привязано')
                                    ->formatStateUsing(fn (OrderModel $record): string => OrderViewPresenter::equipmentLabel($record->equipment_id) ?? '—')
                                    ->visible(fn (OrderModel $record): bool => in_array('repair', $record->service_types ?? [], true)),
                                TextEntry::make('is_warranty')
                                    ->label('Гарантия')
                                    ->badge()
                                    ->formatStateUsing(fn (OrderModel $record): string => $record->is_warranty ? 'Гарантийный' : 'Обычный')
                                    ->color(fn (OrderModel $record): string => $record->is_warranty ? 'warning' : 'gray'),
                                TextEntry::make('problem_description')
                                    ->label('Описание / проблема')
                                    ->placeholder('—')
                                    ->columnSpanFull()
                                    ->visible(fn (OrderModel $record): bool => filled($record->problem_description)),
                                RepeatableEntry::make('tools')
                                    ->label('Инструменты (заточка)')
                                    ->table([
                                        TableColumn::make('Тип'),
                                        TableColumn::make('Кол-во'),
                                    ])
                                    ->schema([
                                        TextEntry::make('tool_type')
                                            ->hiddenLabel()
                                            ->formatStateUsing(fn (string $state): string => OrderViewPresenter::toolTypeLabel($state)),
                                        TextEntry::make('quantity')
                                            ->hiddenLabel(),
                                    ])
                                    ->placeholder('Не указаны')
                                    ->columnSpanFull()
                                    ->visible(fn (OrderModel $record): bool => in_array('sharpening', $record->service_types ?? [], true)),
                            ]),

                        Section::make('Итого')
                            ->icon('heroicon-o-banknotes')
                            ->columnSpan(['default' => 1, 'lg' => 1])
                            ->schema([
                                TextEntry::make('price')
                                    ->label('Сумма заказа')
                                    ->money('RUB')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->placeholder('Не рассчитана')
                                    ->color(fn (?string $state): string => filled($state) ? 'success' : 'gray'),
                                TextEntry::make('source')
                                    ->label('Источник')
                                    ->formatStateUsing(fn (OrderModel $record): string => OrderViewPresenter::sourceLabel($record->source)),
                                TextEntry::make('lead_id')
                                    ->label('Лид')
                                    ->placeholder('—')
                                    ->formatStateUsing(fn (OrderModel $record): string => $record->lead_id !== null ? "#{$record->lead_id}" : '—')
                                    ->visible(fn (OrderModel $record): bool => $record->lead_id !== null),
                                TextEntry::make('created_at')
                                    ->label('Создан')
                                    ->dateTime('d.m.Y H:i'),
                                TextEntry::make('taken_at')
                                    ->label('Взято в работу')
                                    ->dateTime('d.m.Y H:i')
                                    ->placeholder('—')
                                    ->visible(fn (OrderModel $record): bool => $record->taken_at !== null),
                                TextEntry::make('ready_at')
                                    ->label('Готов')
                                    ->dateTime('d.m.Y H:i')
                                    ->placeholder('—')
                                    ->visible(fn (OrderModel $record): bool => $record->ready_at !== null),
                                TextEntry::make('issued_at')
                                    ->label('Выдан')
                                    ->dateTime('d.m.Y H:i')
                                    ->placeholder('—')
                                    ->visible(fn (OrderModel $record): bool => $record->issued_at !== null),
                            ]),
                    ]),

                Section::make('Работы мастера')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->description('Наименования добавляет мастер в POS, цены назначает менеджер')
                    ->headerActions([
                        OrderManageActions::setWorkPrices(),
                    ])
                    ->schema([
                        RepeatableEntry::make('works')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Наименование'),
                                TableColumn::make('Цена')->alignment('end'),
                            ])
                            ->schema([
                                TextEntry::make('description')
                                    ->hiddenLabel(),
                                TextEntry::make('price')
                                    ->hiddenLabel()
                                    ->money('RUB')
                                    ->placeholder('—')
                                    ->color(fn (?string $state): ?string => blank($state) ? 'warning' : null),
                            ])
                            ->placeholder('Мастер ещё не добавил работы'),
                    ]),

                Section::make('Материалы')
                    ->icon('heroicon-o-cube')
                    ->description('Списание со склада — вручную менеджером')
                    ->headerActions([
                        OrderManageActions::addMaterial(),
                        OrderManageActions::removeMaterial(),
                        OrderManageActions::recalculatePrice(),
                    ])
                    ->schema([
                        RepeatableEntry::make('materials')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Позиция'),
                                TableColumn::make('Кол-во'),
                                TableColumn::make('Цена/ед.')->alignment('end'),
                                TableColumn::make('Сумма')->alignment('end'),
                            ])
                            ->schema([
                                TextEntry::make('warehouse_item_id')
                                    ->hiddenLabel()
                                    ->formatStateUsing(fn (int $state): string => OrderViewPresenter::warehouseItemName($state)),
                                TextEntry::make('quantity')
                                    ->hiddenLabel(),
                                TextEntry::make('unit_price')
                                    ->hiddenLabel()
                                    ->money('RUB'),
                                TextEntry::make('total_price')
                                    ->hiddenLabel()
                                    ->money('RUB')
                                    ->weight(FontWeight::Medium),
                            ])
                            ->placeholder('Материалы не добавлены'),
                    ]),

                Section::make('Служебное')
                    ->icon('heroicon-o-information-circle')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('internal_notes')
                            ->label('Внутренние заметки')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('client_id')
                            ->label('ID клиента ЛК')
                            ->placeholder('Гостевой заказ'),
                        TextEntry::make('order_number')
                            ->label('Номер')
                            ->copyable(),
                    ]),
            ]);
    }
}
