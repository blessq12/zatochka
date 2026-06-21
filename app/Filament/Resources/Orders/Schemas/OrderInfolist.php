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

                Section::make('Статус и этап')
                    ->icon('heroicon-o-signal')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('status')
                            ->label('Статус')
                            ->badge()
                            ->color(fn (OrderModel $record): string => OrderViewPresenter::statusColor($record->status))
                            ->icon(fn (OrderModel $record) => OrderViewPresenter::statusIcon($record->status))
                            ->formatStateUsing(fn (OrderModel $record): string => $record->status->label()),
                        TextEntry::make('service_types')
                            ->label('Услуги')
                            ->badge()
                            ->formatStateUsing(function (mixed $state): string {
                                if (is_string($state)) {
                                    return OrderViewPresenter::serviceTypeLabel($state);
                                }

                                return implode(', ', OrderViewPresenter::serviceTypeLabels($state));
                            }),
                        Grid::make(['default' => 2, 'md' => 4])
                            ->columnSpanFull()
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Создан')
                                    ->dateTime('d.m.Y H:i')
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('taken_at')
                                    ->label('В работе')
                                    ->dateTime('d.m.Y H:i')
                                    ->placeholder('—')
                                    ->badge()
                                    ->color(fn (OrderModel $record): string => $record->taken_at !== null ? 'success' : 'gray'),
                                TextEntry::make('ready_at')
                                    ->label('Готов')
                                    ->dateTime('d.m.Y H:i')
                                    ->placeholder('—')
                                    ->badge()
                                    ->color(fn (OrderModel $record): string => $record->ready_at !== null ? 'success' : 'gray'),
                                TextEntry::make('issued_at')
                                    ->label('Выдан')
                                    ->dateTime('d.m.Y H:i')
                                    ->placeholder('—')
                                    ->badge()
                                    ->color(fn (OrderModel $record): string => $record->issued_at !== null ? 'success' : 'gray'),
                            ]),
                    ]),

                Section::make('Клиент')
                    ->icon('heroicon-o-user-circle')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('client_snapshot.full_name')
                            ->label('Имя')
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
                            ->label('Получение')
                            ->badge()
                            ->formatStateUsing(fn (OrderModel $record): string => $record->needs_delivery ? 'Доставка' : 'Самовывоз')
                            ->color(fn (OrderModel $record): string => $record->needs_delivery ? 'info' : 'gray'),
                        TextEntry::make('delivery_address')
                            ->label('Адрес доставки')
                            ->placeholder('—')
                            ->visible(fn (OrderModel $record): bool => $record->needs_delivery)
                            ->columnSpanFull(),
                    ]),

                Section::make('Предмет заказа')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('equipment_id')
                            ->label('Оборудование')
                            ->icon('heroicon-o-cpu-chip')
                            ->placeholder('Не привязано')
                            ->formatStateUsing(fn (OrderModel $record): string => OrderViewPresenter::equipmentLabel($record->equipment_id) ?? '—')
                            ->visible(fn (OrderModel $record): bool => in_array('repair', $record->service_types ?? [], true)),
                        TextEntry::make('is_warranty')
                            ->label('Гарантия')
                            ->badge()
                            ->formatStateUsing(fn (OrderModel $record): string => $record->is_warranty ? 'Гарантийный' : 'Платный')
                            ->color(fn (OrderModel $record): string => $record->is_warranty ? 'warning' : 'gray'),
                        TextEntry::make('warranty_parent_order_id')
                            ->label('Исходный заказ')
                            ->placeholder('—')
                            ->formatStateUsing(function (OrderModel $record): string {
                                if ($record->warranty_parent_order_id === null) {
                                    return '—';
                                }

                                $parent = OrderModel::query()->find($record->warranty_parent_order_id);

                                return $parent?->order_number ?? "#{$record->warranty_parent_order_id}";
                            })
                            ->visible(fn (OrderModel $record): bool => $record->is_warranty),
                        TextEntry::make('problem_description')
                            ->label('Описание / проблема')
                            ->placeholder('—')
                            ->columnSpanFull()
                            ->visible(fn (OrderModel $record): bool => filled($record->problem_description)),
                        RepeatableEntry::make('tools')
                            ->label('Инструменты (заточка)')
                            ->table([
                                TableColumn::make('Наименование'),
                                TableColumn::make('Тип'),
                                TableColumn::make('Кол-во'),
                            ])
                            ->schema([
                                TextEntry::make('name')
                                    ->hiddenLabel()
                                    ->placeholder('—'),
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

                Section::make('Исполнение')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('master_id')
                            ->label('Мастер')
                            ->icon('heroicon-o-user')
                            ->placeholder('Не назначен')
                            ->color(fn (OrderModel $record): ?string => $record->master_id === null ? 'warning' : null)
                            ->formatStateUsing(fn (OrderModel $record): string => OrderViewPresenter::masterName($record->master_id) ?? 'Не назначен'),
                        TextEntry::make('manager_id')
                            ->label('Менеджер')
                            ->icon('heroicon-o-user-circle')
                            ->placeholder('Не назначен')
                            ->formatStateUsing(fn (OrderModel $record): string => OrderViewPresenter::managerName($record->manager_id) ?? 'Не назначен'),
                        TextEntry::make('urgency')
                            ->label('Срочность')
                            ->badge()
                            ->color(fn (OrderModel $record): string => OrderViewPresenter::urgencyColor($record->urgency))
                            ->formatStateUsing(fn (OrderModel $record): string => OrderViewPresenter::urgencyLabel($record->urgency)),
                        TextEntry::make('internal_notes')
                            ->label('Заметки мастера')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('rework_feedback')
                            ->label('Комментарий при возврате')
                            ->placeholder('—')
                            ->color('warning')
                            ->columnSpanFull()
                            ->visible(fn (OrderModel $record): bool => filled($record->rework_feedback)),
                        TextEntry::make('rework_returned_at')
                            ->label('Возвращён на доработку')
                            ->dateTime('d.m.Y H:i')
                            ->visible(fn (OrderModel $record): bool => $record->rework_returned_at !== null),
                    ]),

                Section::make('Состав и стоимость')
                    ->icon('heroicon-o-banknotes')
                    ->description('Цена пересчитывается автоматически при изменении работ или материалов')
                    ->headerActions([
                        OrderManageActions::setWorkPrices(),
                        OrderManageActions::addMaterial(),
                        OrderManageActions::removeMaterial(),
                    ])
                    ->schema([
                        RepeatableEntry::make('works')
                            ->label('Работы')
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
                        RepeatableEntry::make('materials')
                            ->label('Материалы')
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
                        Grid::make(['default' => 1, 'md' => 3])
                            ->schema([
                                TextEntry::make('works_total')
                                    ->label('Работы')
                                    ->formatStateUsing(fn (OrderModel $record): string => OrderViewPresenter::formatMoney(
                                        OrderViewPresenter::worksTotal($record)
                                    )),
                                TextEntry::make('materials_total')
                                    ->label('Материалы')
                                    ->formatStateUsing(fn (OrderModel $record): string => OrderViewPresenter::formatMoney(
                                        OrderViewPresenter::materialsTotal($record)
                                    )),
                                TextEntry::make('price')
                                    ->label('Итого')
                                    ->money('RUB')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->placeholder('Не рассчитана')
                                    ->color(fn (?string $state): string => filled($state) ? 'success' : 'gray'),
                            ]),
                    ]),

                Section::make('Служебное')
                    ->icon('heroicon-o-information-circle')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('source')
                            ->label('Источник')
                            ->formatStateUsing(fn (OrderModel $record): string => OrderViewPresenter::sourceLabel($record->source)),
                        TextEntry::make('lead_id')
                            ->label('Лид')
                            ->placeholder('—')
                            ->formatStateUsing(fn (OrderModel $record): string => $record->lead_id !== null ? "#{$record->lead_id}" : '—'),
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
