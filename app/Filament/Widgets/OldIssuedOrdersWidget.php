<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ClientResource;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class OldIssuedOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'Заказы выданные более 2 недель назад';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $twoWeeksAgo = Carbon::now()->subWeeks(2);

        return $table
            ->query(
                Order::query()
                    ->where('is_deleted', false)
                    ->where('status', Order::STATUS_ISSUED)
                    ->where('updated_at', '<', $twoWeeksAgo)
                    ->with(['client', 'master', 'manager'])
                    ->orderBy('updated_at', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Номер заказа')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->url(fn (Order $record): string => ClientResource::getUrl('view', ['record' => $record->client_id]))
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Дата выдачи')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('calculated_price')
                    ->label('Стоимость')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\IconColumn::make('service_type')
                    ->label('Тип услуги')
                    ->icon(fn(string $state): string => match ($state) {
                        Order::TYPE_SHARPENING => 'heroicon-o-scissors',
                        Order::TYPE_REPAIR => 'heroicon-o-wrench-screwdriver',
                        Order::TYPE_DIAGNOSTIC => 'heroicon-o-magnifying-glass',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        Order::TYPE_REPAIR => 'primary',
                        Order::TYPE_SHARPENING => 'success',
                        Order::TYPE_DIAGNOSTIC => 'warning',
                        default => 'gray',
                    })
                    ->tooltip(fn(Order $record): string => Order::getAvailableTypes()[$record->service_type] ?? $record->service_type),

                Tables\Columns\TextColumn::make('master.name')
                    ->label('Мастер')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('manager.name')
                    ->label('Менеджер')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Открыть')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ])
            ->emptyStateHeading('Нет старых выданных заказов')
            ->emptyStateDescription('Все выданные заказы свежие')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
