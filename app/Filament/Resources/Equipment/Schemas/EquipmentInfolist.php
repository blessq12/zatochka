<?php

namespace App\Filament\Resources\Equipment\Schemas;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Support\EquipmentFormData;
use App\Filament\Support\OrderViewPresenter;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class EquipmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Данные оборудования')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Название')
                            ->weight(FontWeight::Medium),
                        TextEntry::make('brand')
                            ->label('Бренд')
                            ->placeholder('—'),
                        TextEntry::make('model')
                            ->label('Модель')
                            ->placeholder('—'),
                        RepeatableEntry::make('serial_number_rows')
                            ->label('Серийные номера')
                            ->state(fn (EquipmentModel $record): array => EquipmentFormData::displayRows($record->serial_numbers))
                            ->table([
                                TableColumn::make('Компонент'),
                                TableColumn::make('Серийный номер'),
                            ])
                            ->schema([
                                TextEntry::make('component')
                                    ->hiddenLabel()
                                    ->weight(FontWeight::Medium),
                                TextEntry::make('serial')
                                    ->hiddenLabel(),
                            ])
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Заказы с этим оборудованием')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        RepeatableEntry::make('orders')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Номер'),
                                TableColumn::make('Статус'),
                                TableColumn::make('Клиент'),
                                TableColumn::make('Создан'),
                                TableColumn::make('Итого')->alignment('end'),
                            ])
                            ->schema([
                                TextEntry::make('order_number')
                                    ->hiddenLabel()
                                    ->weight(FontWeight::Medium)
                                    ->url(fn (OrderModel $record): string => OrderResource::getUrl('view', ['record' => $record])),
                                TextEntry::make('status')
                                    ->hiddenLabel()
                                    ->badge()
                                    ->color(fn (OrderModel $record): string => OrderViewPresenter::statusColor($record->status))
                                    ->formatStateUsing(fn (OrderModel $record): string => $record->status->label()),
                                TextEntry::make('client_snapshot.full_name')
                                    ->hiddenLabel()
                                    ->placeholder('—')
                                    ->formatStateUsing(fn (OrderModel $record): string => OrderViewPresenter::clientDisplayName($record)),
                                TextEntry::make('created_at')
                                    ->hiddenLabel()
                                    ->dateTime('d.m.Y H:i'),
                                TextEntry::make('price')
                                    ->hiddenLabel()
                                    ->money('RUB')
                                    ->placeholder('—'),
                            ])
                            ->placeholder('Заказов с привязкой к этому оборудованию пока нет'),
                    ]),
            ]);
    }
}
