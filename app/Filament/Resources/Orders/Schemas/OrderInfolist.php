<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Domain\OrderFulfillment\Enum\OrderStatus;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order_number')
                    ->label('Номер'),
                TextEntry::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label()),
                TextEntry::make('price')
                    ->label('Итоговая цена')
                    ->money('RUB')
                    ->placeholder('Не рассчитана'),
                TextEntry::make('client_snapshot.full_name')
                    ->label('Клиент')
                    ->placeholder('—'),
                TextEntry::make('client_snapshot.phone')
                    ->label('Телефон')
                    ->placeholder('—'),
                TextEntry::make('problem_description')
                    ->label('Описание')
                    ->placeholder('—')
                    ->columnSpanFull(),
                RepeatableEntry::make('works')
                    ->label('Работы')
                    ->schema([
                        TextEntry::make('description')
                            ->label('Наименование'),
                        TextEntry::make('price')
                            ->label('Цена')
                            ->money('RUB')
                            ->placeholder('—'),
                    ])
                    ->columnSpanFull(),
                RepeatableEntry::make('materials')
                    ->label('Материалы')
                    ->schema([
                        TextEntry::make('warehouse_item_id')
                            ->label('Склад ID'),
                        TextEntry::make('quantity')
                            ->label('Кол-во'),
                        TextEntry::make('unit_price')
                            ->label('Цена/ед.')
                            ->money('RUB'),
                        TextEntry::make('total_price')
                            ->label('Сумма')
                            ->money('RUB'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
