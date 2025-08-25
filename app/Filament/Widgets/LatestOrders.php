<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 2;

    protected static ?int $sort = 2;

    protected static ?string $heading = 'Последние заказы';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::with('client')->latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('№ заказа')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Сумма')
                    ->money('RUB'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn(Order $record): string => $record->getStatusColor())
                    ->formatStateUsing(fn(string $state): string => Order::getStatusOptions()[$state] ?? $state),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn(Order $record): string => route('filament.crm.resources.orders.edit', $record))
                    ->icon('heroicon-m-eye'),
            ])
            ->paginated(false);
    }
}
