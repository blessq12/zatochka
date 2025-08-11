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
            ->query(Order::with('client')->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('№ заказа')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('ФИО клиента')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.phone')
                    ->label('Телефон')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('client.telegram')
                    ->label('Telegram')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('tool_type')
                    ->label('Инструмент')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_tools_count')
                    ->label('Кол-во')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('client.delivery_address')
                    ->label('Адрес доставки')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('is_paid')
                    ->label('Оплата')
                    ->badge()
                    ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Оплачен' : 'Не оплачен'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn(Order $record): string => $record->getStatusColor())
                    ->formatStateUsing(fn(string $state): string => Order::getStatusOptions()[$state] ?? $state),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'new' => 'Новый',
                        'in_progress' => 'В работе',
                        'ready' => 'Готов',
                        'delivered' => 'Доставлен',
                        'cancelled' => 'Отменен',
                    ]),
                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Оплата')
                    ->placeholder('Все')
                    ->trueLabel('Оплаченные')
                    ->falseLabel('Неоплаченные'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn(Order $record): string => route('filament.admin.resources.orders.edit', $record))
                    ->icon('heroicon-m-eye')
                    ->label('Просмотр'),
                Tables\Actions\Action::make('status')
                    ->icon('heroicon-m-arrow-path')
                    ->label('Статус')
                    ->form([
                        \Filament\Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options([
                                'new' => 'Новый',
                                'in_progress' => 'В работе',
                                'ready' => 'Готов',
                                'delivered' => 'Доставлен',
                                'cancelled' => 'Отменен',
                            ])
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->update(['status' => $data['status']]);
                    }),
            ])
            ->searchable()
            ->paginated(false);
    }
}
