<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'Последние заказы';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->where('is_deleted', false)
                    ->with(['client', 'branch', 'master'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Номер')
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_NEW, Order::STATUS_CONSULTATION, Order::STATUS_DIAGNOSTIC => 'info',
                        Order::STATUS_IN_WORK, Order::STATUS_WAITING_PARTS => 'warning',
                        Order::STATUS_READY => 'success',
                        Order::STATUS_ISSUED => 'success',
                        Order::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Order::getAvailableStatuses()[$state] ?? $state),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Тип услуги')
                    ->formatStateUsing(fn (string $state): string => Order::getAvailableTypes()[$state] ?? $state)
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('master.full_name')
                    ->label('Мастер')
                    ->default('—')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('actual_price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Открыть')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
